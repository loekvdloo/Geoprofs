<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AfdelingVerlofOverzichtController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        // Als geen datum is opgegeven, default naar hele maand of vandaag
        if (!$from) {
            $from = now()->startOfMonth()->toDateString();
        }
        if (!$to) {
            $to = now()->endOfMonth()->toDateString();
        }

        $medewerkers = User::with([
            'aanvragen' => function ($q) use ($from, $to) {
                $q->where(function ($query) use ($from, $to) {
                    $query->whereBetween('start_datum', [$from, $to])
                        ->orWhereBetween('eind_datum', [$from, $to]);
                });
            }
        ])->get();

        return response()->json(['medewerkers' => $medewerkers]);
    }

}
