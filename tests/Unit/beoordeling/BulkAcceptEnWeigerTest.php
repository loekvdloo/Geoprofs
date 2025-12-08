<?php

namespace Tests\Feature;

use App\Models\VerlofAanvraag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkAcceptEnWeigerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_bulk_accept_leave_requests()
    {
        // Maak een test-gebruiker aan
        $user = User::factory()->create();

        $a1 = VerlofAanvraag::create([
            'status' => 'pending',
            'reden' => 'Test',
            'start_datum' => '2024-01-01',
            'eind_datum' => '2024-01-02',
            'user_id' => $user->id,   // VERPLICHT
            'type_id' => 1,           // VERPLICHT als type_id NOT NULL
        ]);

        $a2 = VerlofAanvraag::create([
            'status' => 'pending',
            'reden' => 'Test 2',
            'start_datum' => '2024-02-01',
            'eind_datum' => '2024-02-02',
            'user_id' => $user->id,
            'type_id' => 1,
        ]);

        $this->postJson('/verlof/bulk-accept', [
            'ids' => [$a1->id, $a2->id],
        ])->assertStatus(200);

        $this->assertEquals('accepted', $a1->fresh()->status);
        $this->assertEquals('accepted', $a2->fresh()->status);
    }

    /** @test */
    public function it_can_bulk_reject_leave_requests()
    {
        $user = User::factory()->create();

        $a1 = VerlofAanvraag::create([
            'status' => 'pending',
            'reden' => 'Test',
            'start_datum' => '2024-01-01',
            'eind_datum' => '2024-01-02',
            'user_id' => $user->id,
            'type_id' => 1,
        ]);

        $a2 = VerlofAanvraag::create([
            'status' => 'pending',
            'reden' => 'Test 2',
            'start_datum' => '2024-02-01',
            'eind_datum' => '2024-02-02',
            'user_id' => $user->id,
            'type_id' => 1,
        ]);

        $this->postJson('/verlof/bulk-reject', [
            'ids' => [$a1->id, $a2->id],
        ])->assertStatus(200);

        $this->assertEquals('rejected', $a1->fresh()->status);
        $this->assertEquals('rejected', $a2->fresh()->status);
    }
}
