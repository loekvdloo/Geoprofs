<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class LoginAttemptAdminController extends Controller
{
    /**
     * Geeft de laatste foutieve loginpogingen terug (incl. blokkades).
     * - GEEN nieuwe logs
     * - Alleen succes = false
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Alleen admins mogen dit (nu role_id === 1, later kun je dit aanpassen)
        if (!$user || (int) $user->role_id !== 1) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $attempts = LoginAttempt::with('user')
            ->where('succes', false)
            ->orderByDesc('attempt_time')
            ->limit(200)
            ->get()
            ->map(function ($attempt) {
                $fullName = null;
                if ($attempt->user) {
                    $fullName = trim(
                        ($attempt->user->voornaam ?? '') . ' ' . ($attempt->user->achternaam ?? '')
                    );
                }

                return [
                    'attempt_id'    => $attempt->attempt_id,
                    'attempt_time'  => optional($attempt->attempt_time)->toDateTimeString(),
                    'attempt_ip'    => $attempt->attempt_ip,
                    'failure_reason'=> $attempt->failure_reason,
                    'user_email'    => $attempt->user->email ?? null,
                    'user_name'     => $fullName ?: null,
                ];
            });

        return response()->json(['attempts' => $attempts]);
    }
}
