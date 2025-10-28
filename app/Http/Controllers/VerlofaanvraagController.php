<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreVerlofaanvraagRequest;
use App\Models\Verloftype;
use App\Models\Verlofaanvraag;

class VerlofaanvraagController extends Controller
{
    public function create()
    {
        return view('verlof.create', [
            'types' => Verloftype::orderBy('naam')->get(),
        ]);
    }

    public function store(StoreVerlofaanvraagRequest $request)
    {
        Verlofaanvraag::create([
            'medewerker_id'  => $request->user()->id,
            'verlof_type_id' => $request->verlof_type_id,
            'start_datum'    => $request->start_datum,
            'eind_datum'     => $request->eind_datum,
            'reden'          => $request->reden,
            'aanvraag_datum' => now(),
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Verlofaanvraag ingediend');
    }
}
