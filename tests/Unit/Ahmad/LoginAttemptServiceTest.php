<?php

namespace Tests\Unit\Ahmad;

use App\Models\User;
use App\Services\LoginAttemptService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginAttemptServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use DatabaseMigrations;


    /**
     * TC-LOGIN-REG-01: succesvol login wordt gelogd
     */
    public function test_log_attempt_creates_success_record(): void
    {
        // Arrange: maak een user aan met jouw kolommen
        $userId = DB::table('users')->insertGetId([
            'voornaam'       => 'Medewerker',
            'achternaam'     => 'Test',
            'email'          => 'medewerker@geoprofs.nl',
            'password'       => bcrypt('12345678'),
            'account_status' => true,
            'verlofsaldo'    => 25,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Minimalistische User-instance zodat type-hint klopt (we vermijden model-fillables/PK-issues)
        $user = new User();
        $user->user_id = $userId;

        // Act: log een succesvolle poging via jouw service
        $service = app(LoginAttemptService::class);

        // Signatuur volgens jouw codebasis: recordAttempt(?User $user, string $ip, bool $success, ?string $reason)
        $service->recordAttempt($user, '127.0.0.1', true, null);

        // Assert: er staat een success-record in login_attempts
        $this->assertDatabaseHas('login_attempts', [
            'user_id'        => $userId,
            'succes'         => true,
            'failure_reason' => null,
        ]);
    }

    /**
     * TC-LOGIN-REG-02: Foutieve login wordt gelogd met failure_reason=bad_credentials
     */
    public function test_log_attempt_creates_failure_record(): void
    {
        // ARRANGE: user aanmaken
        $userId = DB::table('users')->insertGetId([
            'voornaam'       => 'Medewerker',
            'achternaam'     => 'Test',
            'email'          => 'medewerker@geoprofs.nl',
            'password'       => bcrypt('12345678'),
            'account_status' => true,
            'verlofsaldo'    => 25,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Minimal User-object zodat type-hint klopt
        $user = new User();
        $user->user_id = $userId;

        // ACT: mislukte poging loggen via jouw service
        $service = app(LoginAttemptService::class);

        // signatuur: recordAttempt(?User $user, string $ip, bool $success, ?string $reason)
        $service->recordAttempt($user, '127.0.0.1', false, 'bad_credentials');

        // ASSERT: logrecord met succes = false en failure_reason = bad_credentials
        $this->assertDatabaseHas('login_attempts', [
            'user_id'        => $userId,
            'succes'         => false,
            'failure_reason' => 'bad_credentials',
        ]);
    }
}
