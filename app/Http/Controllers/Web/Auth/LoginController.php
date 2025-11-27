<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function __construct(private LoginAttemptService $loginService)
    {
    }

    /**
     * Toon loginpagina (Inertia).
     */
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Verwerk login:
     *
     * Flow:
     * 1. Validatie van input
     * 2. User zoeken op e-mail
     * 3. Onbekende e-mail -> poging loggen zonder user_id
     * 4. Check of account al geblokkeerd/inactief is
     * 5. Auth::attempt
     * 6. Bij mislukken: poging loggen + checkAndBlockIfNeeded
     * 7. Bij succes: sessie regenereren + geslaagde poging loggen
     */
    public function store(Request $request)
    {
        // 1. Validatie van de invoer
        $cred = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // IP van de client
        $ip = $request->ip();

        // 2. User ophalen op basis van e-mail
        $user = User::where('email', $cred['email'])->first();

        // 3. Onbekend e-mailadres:
        // - log poging zonder user_id
        // - geef generieke foutmelding
        if (!$user) {
            $this->loginService->recordAttempt(
                null,
                $ip,
                false,
                LoginAttemptService::reasonUnknownEmail()
            );

            return back()
                ->withErrors(['email' => 'Onjuiste inloggegevens.'])
                ->onlyInput('email');
        }

        // 4. Account is al geblokkeerd of inactief:
        // - log mislukte poging met reason 'blocked_account'
        // - laat user niet eens proberen in te loggen
        if ($this->loginService->isUserBlocked($user)) {
            $this->loginService->recordAttempt(
                $user,
                $ip,
                false,
                LoginAttemptService::reasonBlockedAccount()
            );

            return back()
                ->withErrors(['email' => 'Account is geblokkeerd.'])
                ->onlyInput('email');
        }

        // 5. Probeer in te loggen met Laravel Auth
        if (!Auth::attempt($cred, $request->boolean('remember'))) {
            // 5a. Mislukte login -> log een mislukte poging
            $this->loginService->recordAttempt(
                $user,
                $ip,
                false,
                LoginAttemptService::reasonBadCredentials()
            );

            // 5b. Check of dit de 3e opeenvolgende mislukte poging is
            // (service blokkeert dan zelf, en geeft true terug)
            if ($this->loginService->checkAndBlockIfNeeded($user, $ip)) {
                return back()
                    ->withErrors(['email' => 'Account is automatisch geblokkeerd na 3 mislukte pogingen.'])
                    ->onlyInput('email');
            }

            // 5c. Nog niet genoeg mislukte pogingen -> normale foutmelding
            return back()
                ->withErrors(['email' => 'Onjuiste inloggegevens.'])
                ->onlyInput('email');
        }

        // - Login is gelukt:
        // - sessie regenereren
        // - geslaagde poging loggen
        $request->session()->regenerate();
        $this->loginService->recordAttempt(
            $user,
            $ip,
            true,
            null // reason is null bij succes
        );

        // 7. Doorsturen naar intended pagina of dashboard
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log uit.
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
