<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Personnel;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelOrderAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(Role $role, ?Personnel $personnel = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'personnel_id' => $personnel?->id,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'assignment_id' => 'TO-2026-13104',
            'personnel_id' => Personnel::factory()->create()->id,
            'official_station' => 'Regional Office',
            'destination' => 'Cebu City',
            'departure_date' => '2026-11-10',
            'return_date' => '2026-11-12',
        ], $overrides);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard.index'))->assertRedirect(route('login'));
    }

    public function test_staff_cannot_create_travel_orders(): void
    {
        $staff = $this->userWithRole(Role::Staff, Personnel::factory()->create());

        $this->actingAs($staff)->post(route('travel-orders.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('travel_orders', 0);
    }

    public function test_oic_can_create_travel_orders(): void
    {
        $oic = $this->userWithRole(Role::Oic);

        $this->actingAs($oic)->post(route('travel-orders.store'), $this->payload())
            ->assertRedirect(route('dashboard.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('travel_orders', ['assignment_id' => 'TO-2026-13104', 'status' => 'Not started']);
    }

    public function test_assignment_id_format_and_uniqueness_are_enforced(): void
    {
        $oic = $this->userWithRole(Role::Oic);

        $this->actingAs($oic)->post(route('travel-orders.store'), $this->payload(['assignment_id' => 'TO-2025-13104']))
            ->assertSessionHasErrors('assignment_id');

        TravelOrder::factory()->create(['assignment_id' => 'TO-2026-13104']);
        $this->actingAs($oic)->post(route('travel-orders.store'), $this->payload())
            ->assertSessionHasErrors('assignment_id');
    }

    public function test_return_date_cannot_precede_departure(): void
    {
        $oic = $this->userWithRole(Role::Oic);

        $this->actingAs($oic)->post(route('travel-orders.store'), $this->payload([
            'departure_date' => '2026-11-10',
            'return_date' => '2026-11-09',
        ]))->assertSessionHasErrors('return_date');
    }

    public function test_only_oic_or_admin_can_change_status(): void
    {
        $person = Personnel::factory()->create();
        $order = TravelOrder::factory()->for($person, 'personnel')->create(['status' => 'Not started']);

        $staff = $this->userWithRole(Role::Staff, $person);
        $this->actingAs($staff)->patch(route('travel-orders.status', $order), ['status' => 'SUBMITTED'])->assertForbidden();

        $oic = $this->userWithRole(Role::Oic);
        $this->actingAs($oic)->patch(route('travel-orders.status', $order), ['status' => 'SUBMITTED'])->assertRedirect();

        $this->assertSame('SUBMITTED', $order->fresh()->status->value);
    }

    public function test_staff_only_see_their_own_orders(): void
    {
        $mine = Personnel::factory()->create();
        $theirs = Personnel::factory()->create();
        TravelOrder::factory()->for($mine, 'personnel')->create(['assignment_id' => 'TO-2026-11111']);
        TravelOrder::factory()->for($theirs, 'personnel')->create(['assignment_id' => 'TO-2026-22222']);

        $this->actingAs($this->userWithRole(Role::Staff, $mine))
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('TO-2026-11111')
            ->assertDontSee('TO-2026-22222')
            ->assertDontSee('Accomplishment Summary')
            ->assertDontSee('+ New Registry');
    }

    public function test_oic_sees_everything_including_summary_and_new_registry_button(): void
    {
        $a = Personnel::factory()->create();
        $b = Personnel::factory()->create();
        TravelOrder::factory()->for($a, 'personnel')->create(['assignment_id' => 'TO-2026-11111']);
        TravelOrder::factory()->for($b, 'personnel')->create(['assignment_id' => 'TO-2026-22222']);

        $this->actingAs($this->userWithRole(Role::Oic))
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('TO-2026-11111')
            ->assertSee('TO-2026-22222')
            ->assertSee('Accomplishment Summary')
            ->assertSee('+ New Registry');
    }
}
