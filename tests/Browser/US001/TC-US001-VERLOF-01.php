<?php

namespace Tests\Browser\US001;

use App\Models\Role;
use App\Models\User;
use App\Models\Verloftype;
use Database\Seeders\RoleSeeder;
use Database\Seeders\VerloftypeSeeder;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
class SubmitLeaveRequestBrowserTest extends DuskTestCase
{
    use DatabaseTruncation;
    public function test_employee_can_submit_leave_request_and_see_it_in_overview(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(VerloftypeSeeder::class);

        $employeeRole = Role::where('role_naam', 'Medewerker')->firstOrFail();
        $vakantieType = Verloftype::where('naam', 'Vakantie')->firstOrFail();

        $plainPassword = '12345678';

        $employee = User::factory()->create([
            'email'       => 'medewerker@geoprofs.nl',
            'password'    => $plainPassword,
            'role_id'     => $employeeRole->role_id,
            'verlofsaldo' => 25,
        ]);

        $startDatum = '2025-12-10';
        $eindDatum  = '2025-12-12';
        $reden      = 'Vakantie met familie naar Spanje';

        $this->browse(function (Browser $browser) use (
            $employee,
            $plainPassword,
            $vakantieType,
            $startDatum,
            $eindDatum,
            $reden
        ) {
            // LOGIN
            $browser->visit('/login')
                ->waitFor('#email', 5)
                ->type('#email', $employee->email)
                ->type('#password', $plainPassword)
                ->press('Inloggen')
                ->waitForLocation('/dashboard', 5)
                ->assertPathIs('/dashboard');

            // VERLOFAANVRAAG
            $browser->visit('/verlof/aanvraag')
                ->waitForText('Nieuwe verlofaanvraag', 5)
                ->select('verlof_type_id', (string) $vakantieType->verlof_type_id)
                ->type('start_datum', $startDatum)
                ->type('eind_datum', $eindDatum)
                ->type('reden', $reden)
                ->press('Indienen')

                ->waitForText('Mijn verlofaanvragen', 5)
                ->assertSee($reden)
                ->assertSee('pending');
        });
    }
}
