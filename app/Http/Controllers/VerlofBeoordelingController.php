<?php

namespace App\Http\Controllers;

use App\Models\Verlofaanvraag;

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
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lijst van aanvragen"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        $aanvragen = Verlofaanvraag::with(['type', 'medewerker'])
            ->orderByDesc('aanvraag_datum')
            ->get();

        if (request()->wantsJson()) {
            return response()->json($aanvragen);
        }

        return inertia('Beoordeling', [
            'aanvragen' => $aanvragen,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/verlof/beoordeling/{aanvraag}/accept",
     *     summary="Accepteer een verlofaanvraag",
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="aanvraag",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Succesvol geaccepteerd"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function accept(Verlofaanvraag $aanvraag)
    {
        $aanvraag->update(['status' => 'accepted']);

        return response()->json(['message' => 'Verlofaanvraag geaccepteerd']);
    }

    /**
     * @OA\Post(
     *     path="/verlof/beoordeling/{aanvraag}/reject",
     *     summary="Verlofaanvraag afwijzen",
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="aanvraag",
     *         in="path",
     *         required=true,
     *         description="ID van de verlofaanvraag",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Verlofaanvraag afgewezen"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function reject(Verlofaanvraag $aanvraag)
    {
        $aanvraag->update(['status' => 'rejected']);

        return response()->json(['message' => 'Verlofaanvraag afgewezen']);

    }

    public function mijnAanvragen()
    {
        $aanvragen = Verlofaanvraag::with('type')
            ->where('medewerker_id', auth()->id())
            ->orderByDesc('aanvraag_datum')
            ->get();

        return response()->json($aanvragen);
    }
}
