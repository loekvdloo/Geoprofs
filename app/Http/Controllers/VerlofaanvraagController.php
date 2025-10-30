<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVerlofaanvraagRequest;
use App\Models\Verlofaanvraag;

/**
 * @OA\Tag(
 *     name="Verlofaanvraag",
 *     description="API endpoints voor verlof aanvragen"
 * )
 */
class VerlofaanvraagController extends Controller
{
    /**
     * @OA\Post(
     *     path="/verlof/aanvragen",
     *     summary="Verlof aanvragen indienen",
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"verlof_type_id","start_datum","eind_datum","reden"},
     *             @OA\Property(property="verlof_type_id", type="integer"),
     *             @OA\Property(property="start_datum", type="string", format="date"),
     *             @OA\Property(property="eind_datum", type="string", format="date"),
     *             @OA\Property(property="reden", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verlofaanvraag succesvol ingediend"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreVerlofaanvraagRequest $request)
    {
        Verlofaanvraag::create([
            'medewerker_id' => $request->user()->id,
            'verlof_type_id' => $request->verlof_type_id,
            'start_datum' => $request->start_datum,
            'eind_datum' => $request->eind_datum,
            'reden' => $request->reden,
            'aanvraag_datum' => now(),
            'status' => 'pending',
        ]);
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Verlofaanvraag ingediend']);
        }

        return redirect()->back()->with('success', 'Verlofaanvraag ingediend');
    }
}
