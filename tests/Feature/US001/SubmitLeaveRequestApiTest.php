<?php

namespace Tests\Feature\US001;

use Tests\TestCase;
use App\Models\User;
use App\Models\Verloftype;
use App\Models\Verlofaanvraag;
use Database\Seeders\VerloftypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerlofAanvraagMail;

class SubmitLeaveRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_leave_request_is_stored_and_returned(): void
    {
        // 1. Seed verloftypes (Vakantie, Ziekteverlof, ...)
        $this->seed(VerloftypeSeeder::class);

        $verloftype = Verloftype::where('naam', 'Vakantie')->firstOrFail();

        // 2. Maak een medewerker aan
        $employee = User::factory()->create([
            'email'       => 'medewerker@geoprofs.nl',
            'password'    => bcrypt('12345678'),
            'verlofsaldo' => 25,
        ]);

        // 3. Fake mail zodat er niet echt gemaild wordt
        Mail::fake();

        // 4. Act as medewerker via Sanctum en dien verlofaanvraag in via API
        $payload = [
            'verlof_type_id' => $verloftype->verlof_type_id,
            'start_datum'    => '2025-12-10',
            'eind_datum'     => '2025-12-12',
            'reden'          => 'Vakantie met familie naar Spanje',
        ];

        $response = $this
            ->actingAs($employee, 'sanctum')
            ->postJson('/api/verlof/aanvragen', $payload);

        // 5. Response check
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Verlofaanvraag succesvol ingediend',
        ]);

        // 6. Controleer dat de aanvraag in de database staat
        $this->assertDatabaseHas('verlofaanvraag', [
            'user_id'        => $employee->user_id,
            'verlof_type_id' => $verloftype->verlof_type_id,
            'start_datum'    => '2025-12-10 00:00:00',
            'eind_datum'     => '2025-12-12 00:00:00',
            'reden'          => 'Vakantie met familie naar Spanje',
            'status'         => 'pending',
        ]);

        // 7. Optioneel: check dat de mail is verstuurd
        Mail::assertSent(VerlofAanvraagMail::class, function ($mail) use ($employee) {
            return $mail->hasTo($employee->email);
        });
    }
}
