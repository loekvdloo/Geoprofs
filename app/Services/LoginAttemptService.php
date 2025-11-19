<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoginAttemptService
{
    // Hoeveel mislukte pogingen achter elkaar voordat we blokkeren
    private const MAX_FAILED_ATTEMPTS = 3;

    // Standaard reasons zodat je geen typo’s maakt
    private const REASON_BAD_CREDENTIALS   = 'bad_credentials';
    private const REASON_UNKNOWN_EMAIL     = 'unknown_email';
    private const REASON_BLOCKED_ACCOUNT   = 'blocked_account';
    private const REASON_ACCOUNT_BLOCKED_AUTO = 'account_blocked_auto';

    /**
     * Registreert één loginpoging.
     *
     * - $user mag null zijn (bij onbekend e-mailadres)
     * - Bij succes zetten we failure_reason altijd op null
     */
    public function recordAttempt(?User $user, string $ip, bool $success, ?string $reason = null): void
    {
        DB::table('login_attempts')->insert([
            'user_id'        => $user?->user_id,        // kan null zijn bij onbekend e-mail
            'attempt_time'   => now(),                  // timestamp van de poging
            'attempt_ip'     => $ip,                    // IP-adres van de client
            'succes'         => $success,               // true/false -> 1/0 in DB
            'failure_reason' => $success ? null : $reason, // bij succes altijd null
        ]);
    }

    /**
     * Checkt of de gebruiker nu automatisch geblokkeerd moet worden.
     *
     * Voorwaarde:
     * - De LAATSTE 3 pogingen voor deze user
     * - Moeten ALLEMAAL mislukte pogingen zijn
     * - EN met reason 'bad_credentials' (fout wachtwoord)
     *
     * Return:
     * - true  = account is nu geblokkeerd (in deze functie gedaan)
     * - false = niets gedaan
     */
    public function checkAndBlockIfNeeded(User $user, string $ip): bool
    {
        // Pak de laatste 3 attempts voor deze user (ongeacht reden)
        $lastThree = DB::table('login_attempts')
            ->where('user_id', $user->user_id)
            ->orderByDesc('attempt_time')
            ->limit(self::MAX_FAILED_ATTEMPTS)
            ->get(['succes', 'failure_reason']);

        // Als er nog geen 3 attempts zijn, kappen we direct
        if ($lastThree->count() < self::MAX_FAILED_ATTEMPTS) {
            return false;
        }

        // Check of ALLE 3:
        // - gefaald hebben (succes = 0)
        // - EN "bad_credentials" zijn (dus fout wachtwoord)
        $allThreeBadPasswordFails = $lastThree->every(function ($row) {
            return (int) $row->succes === 0
                && $row->failure_reason === self::REASON_BAD_CREDENTIALS;
        });

        // Als één van de 3 success was of een andere reason had,
        // dan blokkeren we NIET.
        if (!$allThreeBadPasswordFails) {
            return false;
        }

        // Nu pas blokkeren we. Dit doen we in een transactie:
        // - user-status updaten
        // - extra attempt loggen voor audit
        DB::transaction(function () use ($user, $ip) {
            DB::table('users')
                ->where('user_id', $user->user_id)
                ->update([
                    'account_status' => false,
                    'updated_at'     => now(),
                ]);

            // Extra login_attempt om vast te leggen dat dit
            // een automatische blokkade was.
            DB::table('login_attempts')->insert([
                'user_id'        => $user->user_id,
                'attempt_time'   => now(),
                'attempt_ip'     => $ip,
                'succes'         => false,
                'failure_reason' => self::REASON_ACCOUNT_BLOCKED_AUTO,
            ]);
        });

        return true;
    }

    /**
     * Handige helper: check of user al geblokkeerd/inactief is.
     *
     * Je kunt zelf bepalen welke flags hier leidend zijn,
     * zolang je het overal hetzelfde gebruikt.
     */
    public function isUserBlocked(User $user): bool
    {
        // account_status === false = account inactief / geblokkeerd
        return $user->account_status === false;
    }

    /**
     * Extra helper om reason-strings op één plek te houden.
     */
    public static function reasonBadCredentials(): string
    {
        return self::REASON_BAD_CREDENTIALS;
    }

    public static function reasonUnknownEmail(): string
    {
        return self::REASON_UNKNOWN_EMAIL;
    }

    public static function reasonBlockedAccount(): string
    {
        return self::REASON_BLOCKED_ACCOUNT;
    }
}
