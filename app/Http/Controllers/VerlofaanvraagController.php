<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVerlofaanvraagRequest;
use App\Models\Verlofaanvraag;
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
     *     summary="Verlof aanvragen indienen",
     *     tags={"Verlof"},
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store(StoreVerlofaanvraagRequest $request)
    {
        $user = $request->user();

        $aanvraag = Verlofaanvraag::create([
            'medewerker_id' => $user->id,
            'verlof_type_id' => $request->verlof_type_id,
            'start_datum' => $request->start_datum,
            'eind_datum' => $request->eind_datum,
            'reden' => $request->reden,
            'aanvraag_datum' => now(),
            'status' => 'pending',
        ]);

        // Mail naar de aanvrager
        Mail::to($user->email)->send(new VerlofAanvraagMail($aanvraag));

        return response()->json([
            'message' => 'Verlofaanvraag succesvol ingediend.',
            'aanvraag' => $aanvraag,
        ]);
    }
}
