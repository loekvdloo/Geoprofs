<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BezettingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless((int) $user->role_id === 3, 403);
        abort_unless(!is_null($user->afdeling_id), 403);

        $afdelingId = (int) $user->afdeling_id;

        $from = Carbon::parse($request->query('from', now()->startOfWeek()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->query('to', now()->endOfWeek()->toDateString()))->endOfDay();

        $totalEmployees = DB::table('users')
            ->where('afdeling_id', $afdelingId)
            ->where('role_id', 2)
            ->count();

        $requests = DB::table('verlofaanvraag as v')
            ->join('users as u', 'u.user_id', '=', 'v.user_id')
            ->where('u.afdeling_id', $afdelingId)
            ->where('u.role_id', 2)
            ->where('v.status', 'accepted')
            ->whereDate('v.start_datum', '<=', $to->toDateString())
            ->whereDate('v.eind_datum', '>=', $from->toDateString())
            ->select(['v.user_id', 'v.start_datum', 'v.eind_datum'])
            ->get();

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
            'days' => $days,
        ]);
    }

    public function day(Request $request)
    {
        $user = $request->user();

        abort_unless((int) $user->role_id === 3, 403);
        abort_unless(!is_null($user->afdeling_id), 403);

        $afdelingId = (int) $user->afdeling_id;

        $date = Carbon::parse($request->query('date', now()->toDateString()))->toDateString();

        $employees = DB::table('users')
            ->where('afdeling_id', $afdelingId)
            ->where('role_id', 2)
            ->select(['user_id', 'voornaam', 'achternaam', 'email'])
            ->orderBy('voornaam')
            ->orderBy('achternaam')
            ->get();

        $absences = DB::table('verlofaanvraag as v')
            ->join('users as u', 'u.user_id', '=', 'v.user_id')
            ->where('u.afdeling_id', $afdelingId)
            ->where('u.role_id', 2)
            ->where('v.status', 'accepted')
            ->whereDate('v.start_datum', '<=', $date)
            ->whereDate('v.eind_datum', '>=', $date)
            ->select([
                'v.user_id',
                'v.start_datum',
                'v.eind_datum',
                'v.reden',
            ])
            ->get()
            ->keyBy('user_id');

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
            'employees' => $list,
        ]);
    }
}
