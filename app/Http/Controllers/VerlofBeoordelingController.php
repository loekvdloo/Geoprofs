<?php

namespace App\Http\Controllers;

use App\Models\Verlofaanvraag;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerlofStatusMail;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="VerlofBeoordeling",
 *     description="API endpoints voor het beoordelen van verlof aanvragen"
 * )
 */
class VerlofBeoordelingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/verlof/beoordeling",
     *     summary="Alle verlofaanvragen ophalen voor beoordeling",
     *     tags={"VerlofBeoordeling"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lijst van verlofaanvragen",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="aanvraag_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=42),
     *                 @OA\Property(
     *                     property="medewerker",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=42),
     *                     @OA\Property(property="voornaam", type="string", example="Loek"),
     *                     @OA\Property(property="achternaam", type="string", example="Jansen"),
     *                     @OA\Property(property="email", type="string", example="loek@loek.nl")
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="object",
     *                     @OA\Property(property="verlof_type_id", type="integer", example=2),
     *                     @OA\Property(property="naam", type="string", example="Ziekteverlof")
     *                 ),
     *                 @OA\Property(property="start_datum", type="string", format="date", example="2025-11-10"),
     *                 @OA\Property(property="eind_datum", type="string", format="date", example="2025-11-12"),
     *                 @OA\Property(property="reden", type="string", example="Ziekte"),
     *                 @OA\Property(property="aanvraag_datum", type="string", format="date-time", example="2025-11-05T15:00:00Z"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Niet geautoriseerd")
     * )
     */
    public function index()
    {
        $user = auth()->user();

        $query = Verlofaanvraag::with(['type', 'medewerker'])
            ->orderByDesc('aanvraag_datum');

        if ((int) $user->role_id === 3) {
            $query->whereHas('medewerker', function ($q) use ($user) {
                $q->where('afdeling_id', $user->afdeling_id);
            });
        }

        return response()->json($query->get());
    }

    /**
     * @OA\Post(
     *     path="/verlof/beoordeling/{aanvraag}/accept",
     *     summary="Accepteer een verlofaanvraag",
     *     tags={"VerlofBeoordeling"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="aanvraag",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verlofaanvraag geaccepteerd",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Verlofaanvraag geaccepteerd")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Niet geautoriseerd")
     * )
     */
    public function accept(Verlofaanvraag $aanvraag)
    {
        if ($aanvraag->status !== 'pending') {
            return response()->json(['message' => 'Deze aanvraag is al beoordeeld.'], 400);
        }

        $medewerker = $aanvraag->medewerker;

        $start = new \DateTime($aanvraag->start_datum);
        $eind = new \DateTime($aanvraag->eind_datum);
        $dagen = $eind->diff($start)->days + 1;

        $medewerker->verlofsaldo = max(0, $medewerker->verlofsaldo - $dagen);
        $medewerker->save();

        $aanvraag->update(['status' => 'accepted']);

        if ($medewerker && $medewerker->email) {
            Mail::to($medewerker->email)
                ->send(new VerlofStatusMail($aanvraag, 'accepted'));
        }

        return response()->json([
            'message' => 'Verlofaanvraag geaccepteerd en saldo aangepast.',
            'nieuwe_saldo' => $medewerker->verlofsaldo,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/verlof/beoordeling/{aanvraag}/reject",
     *     summary="Verlofaanvraag afwijzen",
     *     tags={"VerlofBeoordeling"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="aanvraag",
     *         in="path",
     *         required=true,
     *         description="ID van de verlofaanvraag",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verlofaanvraag afgewezen",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Verlofaanvraag afgewezen")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Niet geautoriseerd")
     * )
     */
    public function reject(Verlofaanvraag $aanvraag)
    {
        if ($aanvraag->status !== 'pending') {
            return response()->json(['message' => 'Deze aanvraag is al beoordeeld.'], 400);
        }

        $aanvraag->update(['status' => 'rejected']);

        if ($aanvraag->medewerker && $aanvraag->medewerker->email) {
            Mail::to($aanvraag->medewerker->email)
                ->send(new VerlofStatusMail($aanvraag, 'rejected'));
        }

        return response()->json(['message' => 'Verlofaanvraag afgewezen.']);
    }

    /**
     * @OA\Get(
     *     path="/verlof/mijn-aanvragen",
     *     summary="Haal de verlofaanvragen van de ingelogde medewerker op",
     *     tags={"VerlofBeoordeling"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lijst van verlofaanvragen succesvol opgehaald",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="aanvraag_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=42),
     *                 @OA\Property(
     *                     property="type",
     *                     type="object",
     *                     @OA\Property(property="verlof_type_id", type="integer", example=2),
     *                     @OA\Property(property="naam", type="string", example="Ziekteverlof")
     *                 ),
     *                 @OA\Property(property="start_datum", type="string", format="date", example="2025-11-10"),
     *                 @OA\Property(property="eind_datum", type="string", format="date", example="2025-11-12"),
     *                 @OA\Property(property="reden", type="string", example="Ziekte"),
     *                 @OA\Property(property="aanvraag_datum", type="string", format="date-time", example="2025-11-05T15:00:00Z"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Niet geautoriseerd"),
     *     @OA\Response(response=500, description="Serverfout")
     * )
     */
    public function mijnAanvragen()
    {
        $aanvragen = Verlofaanvraag::with('type')
            ->where('user_id', auth()->id())
            ->orderByDesc('aanvraag_datum')
            ->get();

        return response()->json($aanvragen);
    }

    // ===================== BULK ACTIES ===================== //

    /**
     * Bulk accepteren van verlofaanvragen
     */
    public function bulkAccept(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:verlofaanvraag,aanvraag_id',
        ]);

        $aanvragen = Verlofaanvraag::with('medewerker')
            ->whereIn('aanvraag_id', $request->ids)
            ->where('status', 'pending')
            ->get();

        foreach ($aanvragen as $aanvraag) {
            $medewerker = $aanvraag->medewerker;

            if ($medewerker) {
                $start = new \DateTime($aanvraag->start_datum);
                $eind = new \DateTime($aanvraag->eind_datum);
                $dagen = $eind->diff($start)->days + 1;

                $medewerker->verlofsaldo = max(0, $medewerker->verlofsaldo - $dagen);
                $medewerker->save();

                $aanvraag->update(['status' => 'accepted']);

                if ($medewerker->email) {
                    Mail::to($medewerker->email)
                        ->send(new VerlofStatusMail($aanvraag, 'accepted'));
                }
            }
        }

        return response()->json([
            'message' => count($aanvragen) . ' aanvragen succesvol geaccepteerd.'
        ]);
    }

    /**
     * Bulk weigeren van verlofaanvragen
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:verlofaanvraag,aanvraag_id',
        ]);

        $aanvragen = Verlofaanvraag::with('medewerker')
            ->whereIn('aanvraag_id', $request->ids)
            ->where('status', 'pending')
            ->get();

        foreach ($aanvragen as $aanvraag) {
            $aanvraag->update(['status' => 'rejected']);

            if ($aanvraag->medewerker && $aanvraag->medewerker->email) {
                Mail::to($aanvraag->medewerker->email)
                    ->send(new VerlofStatusMail($aanvraag, 'rejected'));
            }
        }

        return response()->json([
            'message' => count($aanvragen) . ' aanvragen succesvol geweigerd.'
        ]);
    }
}
