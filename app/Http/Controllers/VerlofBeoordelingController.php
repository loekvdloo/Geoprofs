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
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        $aanvragen = Verlofaanvraag::with(['type', 'medewerker'])
            ->orderByDesc('aanvraag_datum')
            ->get();

        return response()->json($aanvragen);
    }

    /**
     * @OA\Post(
     *     path="/verlof/beoordeling/{aanvraag}/accept",
     *     summary="Accepteer een verlofaanvraag",
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function accept(Verlofaanvraag $aanvraag)
    {
        if ($aanvraag->status !== 'pending') {
            return response()->json(['message' => 'Deze aanvraag is al beoordeeld.'], 400);
        }

        $medewerker = $aanvraag->medewerker;

        // ✅ Bereken aantal dagen tussen start en eind (inclusief startdag)
        $start = new \DateTime($aanvraag->start_datum);
        $eind = new \DateTime($aanvraag->eind_datum);
        $dagen = $eind->diff($start)->days + 1;

        // ✅ Verlaag verlofsaldo
        $medewerker->verlofsaldo = max(0, $medewerker->verlofsaldo - $dagen);
        $medewerker->save();

        // ✅ Update status naar accepted
        $aanvraag->update(['status' => 'accepted']);

        // ✅ Verstuur bevestiging via mail
        Mail::to($medewerker->email)->send(new VerlofStatusMail($aanvraag, 'accepted'));

        return response()->json([
            'message' => 'Verlofaanvraag geaccepteerd en saldo aangepast.',
            'nieuwe_saldo' => $medewerker->verlofsaldo,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/verlof/beoordeling/{aanvraag}/reject",
     *     summary="Verlofaanvraag afwijzen",
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function reject(Verlofaanvraag $aanvraag)
    {
        if ($aanvraag->status !== 'pending') {
            return response()->json(['message' => 'Deze aanvraag is al beoordeeld.'], 400);
        }

        $aanvraag->update(['status' => 'rejected']);

        Mail::to($aanvraag->medewerker->email)
            ->send(new VerlofStatusMail($aanvraag, 'rejected'));

        return response()->json(['message' => 'Verlofaanvraag afgewezen.']);
    }

    /**
     * @OA\Get(
     *     path="/mijn-aanvragen",
     *     summary="Haal de verlofaanvragen van de ingelogde medewerker op",
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function mijnAanvragen()
    {
        $aanvragen = Verlofaanvraag::with('type')
            ->where('medewerker_id', auth()->id())
            ->orderByDesc('aanvraag_datum')
            ->get();

        return response()->json($aanvragen);
    }
}
