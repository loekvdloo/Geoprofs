<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVerlofaanvraagRequest;
use App\Models\Verlofaanvraag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerlofAanvraagMail;
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
     *     summary="Dien een verlofaanvraag in",
     *     tags={"Verlofaanvraag"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"verlof_type_id","start_datum","eind_datum","reden"},
     *             @OA\Property(property="verlof_type_id", type="integer", example=2, description="ID van het verloftype"),
     *             @OA\Property(property="start_datum", type="string", format="date", example="2025-11-10", description="Startdatum van het verlof"),
     *             @OA\Property(property="eind_datum", type="string", format="date", example="2025-11-12", description="Einddatum van het verlof"),
     *             @OA\Property(property="reden", type="string", example="Ziekte", description="Reden voor het verlof")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verlofaanvraag succesvol ingediend",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="aanvraag", type="object",
     *                 @OA\Property(property="aanvraag_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=42),
     *                 @OA\Property(property="verlof_type_id", type="integer", example=2),
     *                 @OA\Property(property="start_datum", type="string", format="date", example="2025-11-10"),
     *                 @OA\Property(property="eind_datum", type="string", format="date", example="2025-11-12"),
     *                 @OA\Property(property="reden", type="string", example="Ziekte"),
     *                 @OA\Property(property="aanvraag_datum", type="string", format="date-time", example="2025-11-05T15:00:00Z"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             ),
     *             @OA\Property(property="message", type="string", example="Verlofaanvraag succesvol ingediend")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Niet geautoriseerd")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'verlof_type_id' => 'required|exists:verloftype,verlof_type_id',
            'start_datum' => 'required|date',
            'eind_datum' => 'required|date|after_or_equal:start_datum',
            'reden' => 'required|string',
        ]);

        $aanvraag = Verlofaanvraag::create([
            'user_id' => auth()->id(),
            'verlof_type_id' => $request->verlof_type_id,
            'start_datum' => $request->start_datum,
            'eind_datum' => $request->eind_datum,
            'reden' => $request->reden,
            'aanvraag_datum' => now(),
            'status' => 'pending',
        ]);

        Mail::to($request->user()->email)->send(new VerlofAanvraagMail($aanvraag));

        return response()->json([
            'aanvraag' => $aanvraag,
            'message' => 'Verlofaanvraag succesvol ingediend',
        ]);
    }
}
    