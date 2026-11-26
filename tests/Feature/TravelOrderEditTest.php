<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Personnel;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelOrderEditTest extends TestCase
{
    use RefreshDatabase;

    private function actor(Role $role, ?Personnel $personnel = null): User
    {
        return User::factory()->create(['role' => $role, 'personnel_id' => $personnel?->id]);
    }

    private function order(array $overrides = []): TravelOrder
    {
        return TravelOrder::factory()->create(array_merge([
            'assignment_id' => 'TO-2026-11111',
            'departure_date' => '2026-11-10',
            'return_date' => '2026-11-12',
        ], $overrides));
    }

    private function payload(TravelOrder $order, array $overrides = []): array
    {
        return array_merge([
            'assignment_id' => $order->assignment_id,
            'personnel_id' => $order->personnel_id,
            'official_station' => 'Regional Office',
            'destination' => 'Cebu City',
            'departure_date' => '2026-11-10',
            'return_date' => '2026-11-12',
        ], $overrides);
    }

    public function test_staff_cannot_edit_even_their_own_order(): void
    {
        $person = Personnel::factory()->create();
        $order = $this->order(['personnel_id' => $person->id]);
        $staff = $this->actor(Role::Staff, $person);

        $this->actingAs($staff)->get(route('travel-orders.edit', $order))->assertForbidden();
        $this->actingAs($staff)->put(route('travel-orders.update', $order), $this->payload($order, ['destination' => 'Hacked']))
            ->assertForbidden();

        $this->assertNotSame('Hacked', $order->fresh()->destination);
    }

    public function test_oic_can_open_the_edit_page(): void
    {
        $order = $this->order();

        $this->actingAs($this->actor(Role::Oic))->get(route('travel-orders.edit', $order))
            ->assertOk()
            ->assertSee('TO-2026-11111');
    }

    public function test_oic_can_update_a_travel_order(): void
    {
        $order = $this->order();
        $other = Personnel::factory()->create();

        $this->actingAs($this->actor(Role::Oic))->put(route('travel-orders.update', $order), $this->payload($order, [
            'assignment_id' => ' to-2026-22222 ',
            'personnel_id' => $other->id,
            'destination' => 'Davao City',
            'return_date' => '2026-11-15',
        ]))->assertRedirect(route('dashboard.index'))->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('TO-2026-22222', $order->assignment_id);
        $this->assertSame($other->id, $order->personnel_id);
        $this->assertSame('Davao City', $order->destination);
        $this->assertSame('2026-11-15', $order->return_date->toDateString());
    }

    public function test_admin_can_update_a_travel_order(): void
    {
        $order = $this->order();

        $this->actingAs($this->actor(Role::Admin))->put(route('travel-orders.update', $order), $this->payload($order, ['destination' => 'Iloilo City']))
            ->assertRedirect(route('dashboard.index'));

        $this->assertSame('Iloilo City', $order->fresh()->destination);
    }

    public function test_keeping_the_same_assignment_id_is_allowed(): void
    {
        $order = $this->order();

        $this->actingAs($this->actor(Role::Oic))->put(route('travel-orders.update', $order), $this->payload($order))
            ->assertSessionHasNoErrors();
    }

    public function test_assignment_id_cannot_clash_with_another_order(): void
    {
        $this->order(['assignment_id' => 'TO-2026-99999']);
        $order = $this->order();

        $this->actingAs($this->actor(Role::Oic))->put(route('travel-orders.update', $order), $this->payload($order, ['assignment_id' => 'TO-2026-99999']))
            ->assertSessionHasErrors('assignment_id');
    }

    public function test_validation_rules_still_apply_when_editing(): void
    {
        $order = $this->order();
        $oic = $this->actor(Role::Oic);

        $this->actingAs($oic)->put(route('travel-orders.update', $order), $this->payload($order, ['assignment_id' => 'TO-2025-12345']))
            ->assertSessionHasErrors('assignment_id');

        $this->actingAs($oic)->put(route('travel-orders.update', $order), $this->payload($order, [
            'departure_date' => '2026-11-10',
            'return_date' => '2026-11-09',
        ]))->assertSessionHasErrors('return_date');
    }

    public function test_dashboard_shows_edit_link_to_oic_but_not_to_staff(): void
    {
        $person = Personnel::factory()->create();
        $order = $this->order(['personnel_id' => $person->id]);
        $editUrl = route('travel-orders.edit', $order);

        $this->actingAs($this->actor(Role::Oic))->get(route('dashboard.index'))->assertSee($editUrl);
        $this->actingAs($this->actor(Role::Staff, $person))->get(route('dashboard.index'))->assertDontSee($editUrl);
    }
}
