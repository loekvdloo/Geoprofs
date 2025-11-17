<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoginAttemptService
{
    /**
     * Registreert één loginpoging.
     */
    public function recordAttempt(?User $user, string $ip, bool $success, ?string $reason = null): void
    {
        // bij succes is failure_reason altijd null
        DB::table('login_attempts')->insert([
            'user_id'        => $user?->user_id,
            'attempt_time'   => now(),
            'attempt_ip'     => $ip,
            'succes'         => $success,
            'failure_reason' => $success ? null : $reason,
        ]);

        // Alleen kijken naar blokkeren als:
        // - er een user is
        // - deze poging mislukt is
        if ($user && !$success) {
            $this->checkForBlock($user);
        }
    }

    /**
     * Blokkeert user alleen als de laatste 3 pogingen achter elkaar fout zijn.
     */
    protected function checkForBlock(User $user): void
    {
        $lastAttempts = DB::table('login_attempts')
            ->where('user_id', $user->user_id)
            ->orderByDesc('attempt_time')
            ->limit(3)
            ->get(['attempt_id', 'succes']);

        if ($lastAttempts->count() < 3) {
            return;
        }

        $allFailed = $lastAttempts->every(fn($a) => (int)$a->succes === 0);

        if (!$allFailed) {
            return;
        }

        // 3 pogingen fout → blokkeer user
        $user->account_status = false;
        $user->save();

        // Markeer alleen de laatste als auto-block
        DB::table('login_attempts')
            ->where('attempt_id', $lastAttempts->first()->attempt_id)
            ->update(['failure_reason' => 'account_blocked_auto']);
    }
}
