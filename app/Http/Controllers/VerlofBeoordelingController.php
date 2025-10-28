<?php

namespace App\Http\Controllers;

use App\Models\Verlofaanvraag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerlofBeoordelingController extends Controller
{
    public function index()
    {
        $aanvragen = Verlofaanvraag::with(['type', 'medewerker'])
            ->orderByDesc('aanvraag_datum')
            ->get();

        return Inertia::render('Verlof/beoordeling', [
            'aanvragen' => $aanvragen,
        ]);
    }

    public function accept(Verlofaanvraag $aanvraag)
    {
        $aanvraag->update(['status' => 'accepted']);
        return back()->with('success', 'Verlofaanvraag geaccepteerd');
    }

    public function reject(Verlofaanvraag $aanvraag)
    {
        $aanvraag->update(['status' => 'rejected']);
        return back()->with('success', 'Verlofaanvraag afgewezen');
    }
}
