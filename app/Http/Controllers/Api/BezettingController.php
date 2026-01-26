<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BezettingController extends Controller
{
    /**
     * Bezetting per dag in een periode.
     *
     * Manager (role_id=3): altijd eigen afdeling.
     * Admin (role_id=1): standaard alle afdelingen, optioneel filter via ?afdeling_id=...
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Alleen Admin (1) en Manager (3)
        abort_unless(in_array((int) $user->role_id, [1, 3], true), 403);

        // Periode
        $from = Carbon::parse($request->query('from', now()->startOfWeek()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->query('to', now()->endOfWeek()->toDateString()))->endOfDay();

        // Afdeling bepalen:
        // - Manager: forced eigen afdeling
        // - Admin: optioneel query param (leeg = alle afdelingen)
        $afdelingId = null;

        if ((int) $user->role_id === 3) {
            abort_unless(!is_null($user->afdeling_id), 403);
            $afdelingId = (int) $user->afdeling_id;
        } else {
            $qAfdeling = $request->query('afdeling_id');
            if (!is_null($qAfdeling) && $qAfdeling !== '') {
                $afdelingId = (int) $qAfdeling;
            }
        }

        // Totaal medewerkers tellen (role_id = 2) - gefilterd als afdelingId is gezet
        $totalEmployeesQuery = DB::table('users')
            ->where('role_id', 2);

        if (!is_null($afdelingId)) {
            $totalEmployeesQuery->where('afdeling_id', $afdelingId);
        }

        $totalEmployees = $totalEmployeesQuery->count();

        // Geaccepteerde verlofaanvragen binnen periode - gefilterd als afdelingId is gezet
        $requestsQuery = DB::table('verlofaanvraag as v')
            ->join('users as u', 'u.user_id', '=', 'v.user_id')
            ->where('u.role_id', 2)
            ->where('v.status', 'accepted')
            ->whereDate('v.start_datum', '<=', $to->toDateString())
            ->whereDate('v.eind_datum', '>=', $from->toDateString())
            ->select(['v.user_id', 'v.start_datum', 'v.eind_datum']);

        if (!is_null($afdelingId)) {
            $requestsQuery->where('u.afdeling_id', $afdelingId);
        }

        $requests = $requestsQuery->get();

        // Afwezigen per dag (unique op user_id)
        $absentByDay = [];

        foreach ($requests as $r) {
            $period = CarbonPeriod::create(
                Carbon::parse($r->start_datum)->max($from)->startOfDay(),
                Carbon::parse($r->eind_datum)->min($to)->startOfDay()
            );

            foreach ($period as $day) {
                $key = $day->toDateString();
                $absentByDay[$key] ??= [];
                $absentByDay[$key][$r->user_id] = true;
            }
        }

        // Output days
        $days = [];
        $period = CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay());

        foreach ($period as $day) {
            $date = $day->toDateString();
            $absentCount = isset($absentByDay[$date]) ? count($absentByDay[$date]) : 0;

            $days[] = [
                'date' => $date,
                'absent_count' => $absentCount,
                'present_count' => max(0, $totalEmployees - $absentCount),
                'total_employees' => $totalEmployees,
            ];
        }

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'afdeling_id' => $afdelingId, // null => alle afdelingen (admin)
            'days' => $days,
        ]);
    }

    /**
     * Detail lijst per medewerker op een specifieke dag.
     *
     * Manager (role_id=3): altijd eigen afdeling.
     * Admin (role_id=1): standaard alle afdelingen, optioneel filter via ?afdeling_id=...
     */
    public function day(Request $request)
    {
        $user = $request->user();

        // Alleen Admin (1) en Manager (3)
        abort_unless(in_array((int) $user->role_id, [1, 3], true), 403);

        // Afdeling bepalen:
        // - Manager: forced eigen afdeling
        // - Admin: optioneel query param (leeg = alle afdelingen)
        $afdelingId = null;

        if ((int) $user->role_id === 3) {
            abort_unless(!is_null($user->afdeling_id), 403);
            $afdelingId = (int) $user->afdeling_id;
        } else {
            $qAfdeling = $request->query('afdeling_id');
            if (!is_null($qAfdeling) && $qAfdeling !== '') {
                $afdelingId = (int) $qAfdeling;
            }
        }

        $date = Carbon::parse($request->query('date', now()->toDateString()))->toDateString();

        // Medewerkers ophalen (role_id=2), gefilterd als afdelingId is gezet
        $employeesQuery = DB::table('users')
            ->where('role_id', 2)
            ->select(['user_id', 'voornaam', 'achternaam', 'email'])
            ->orderBy('voornaam')
            ->orderBy('achternaam');

        if (!is_null($afdelingId)) {
            $employeesQuery->where('afdeling_id', $afdelingId);
        }

        $employees = $employeesQuery->get();

        // Afwezigheden op die datum (accepted), gefilterd als afdelingId is gezet
        $absencesQuery = DB::table('verlofaanvraag as v')
            ->join('users as u', 'u.user_id', '=', 'v.user_id')
            ->where('u.role_id', 2)
            ->where('v.status', 'accepted')
            ->whereDate('v.start_datum', '<=', $date)
            ->whereDate('v.eind_datum', '>=', $date)
            ->select([
                'v.user_id',
                'v.start_datum',
                'v.eind_datum',
                'v.reden',
            ]);

        if (!is_null($afdelingId)) {
            $absencesQuery->where('u.afdeling_id', $afdelingId);
        }

        $absences = $absencesQuery->get()->keyBy('user_id');

        $list = [];

        foreach ($employees as $e) {
            $name = trim(($e->voornaam ?? '') . ' ' . ($e->achternaam ?? '')) ?: $e->email;

            if (isset($absences[$e->user_id])) {
                $a = $absences[$e->user_id];

                $list[] = [
                    'user_id' => $e->user_id,
                    'name' => $name,
                    'status' => 'absent',
                    'reason' => $a->reden,
                    'start_date' => Carbon::parse($a->start_datum)->toDateString(),
                    'end_date' => Carbon::parse($a->eind_datum)->toDateString(),
                ];
            } else {
                $list[] = [
                    'user_id' => $e->user_id,
                    'name' => $name,
                    'status' => 'present',
                    'reason' => null,
                    'start_date' => null,
                    'end_date' => null,
                ];
            }
        }

        return response()->json([
            'date' => $date,
            'afdeling_id' => $afdelingId, // null => alle afdelingen (admin)
            'employees' => $list,
        ]);
    }
}
