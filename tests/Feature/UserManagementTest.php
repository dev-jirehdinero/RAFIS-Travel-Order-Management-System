<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actor(Role $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'DELA CRUZ, JUAN P.',
            'email' => 'juan@example.com',
            'password' => 'temp-pass-1',
            'role' => 'staff',
            'position' => 'Information Officer I',
        ], $overrides);
    }

    public function test_staff_cannot_see_or_create_accounts(): void
    {
        $staff = $this->actor(Role::Staff);

        $this->actingAs($staff)->get(route('users.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('users.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('users.store'), $this->payload())->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    public function test_oic_can_open_the_accounts_pages(): void
    {
        $oic = $this->actor(Role::Oic);

        $this->actingAs($oic)->get(route('users.index'))->assertOk()->assertSee('+ New account');
        $this->actingAs($oic)->get(route('users.create'))->assertOk();
    }

    public function test_oic_creates_a_staff_account_with_a_new_staff_record(): void
    {
        $oic = $this->actor(Role::Oic);

        $this->actingAs($oic)->post(route('users.store'), $this->payload())
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $user = User::where('email', 'juan@example.com')->firstOrFail();
        $this->assertSame(Role::Staff, $user->role);
        $this->assertTrue(Hash::check('temp-pass-1', $user->password));
        $this->assertNotNull($user->personnel_id);
        $this->assertDatabaseHas('personnel', [
            'id' => $user->personnel_id,
            'name' => 'DELA CRUZ, JUAN P.',
            'position' => 'Information Officer I',
        ]);
    }

    public function test_oic_cannot_create_oic_or_admin_accounts(): void
    {
        $oic = $this->actor(Role::Oic);

        foreach (['oic', 'admin'] as $role) {
            $this->actingAs($oic)->post(route('users.store'), $this->payload(['role' => $role]))
                ->assertSessionHasErrors('role');
        }

        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
    }

    public function test_admin_can_create_oic_and_admin_accounts(): void
    {
        $admin = $this->actor(Role::Admin);

        $this->actingAs($admin)->post(route('users.store'), $this->payload(['role' => 'oic']))
            ->assertRedirect(route('users.index'));
        $this->assertSame(Role::Oic, User::where('email', 'juan@example.com')->first()->role);

        // Admin accounts don't get a staff record, so no position is needed.
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Second Admin',
            'email' => 'admin2@example.com',
            'password' => 'temp-pass-1',
            'role' => 'admin',
        ])->assertRedirect(route('users.index'));

        $second = User::where('email', 'admin2@example.com')->first();
        $this->assertSame(Role::Admin, $second->role);
        $this->assertNull($second->personnel_id);
    }

    public function test_account_can_be_linked_to_an_existing_staff_record(): void
    {
        $oic = $this->actor(Role::Oic);
        $person = Personnel::factory()->create();

        $this->actingAs($oic)->post(route('users.store'), $this->payload([
            'personnel_id' => $person->id,
            'position' => null,
        ]))->assertRedirect(route('users.index'));

        $this->assertSame($person->id, User::where('email', 'juan@example.com')->first()->personnel_id);
        $this->assertDatabaseCount('personnel', 1); // no extra record created
    }

    public function test_a_staff_record_can_only_have_one_account(): void
    {
        $oic = $this->actor(Role::Oic);
        $person = Personnel::factory()->create();
        User::factory()->create(['personnel_id' => $person->id]);

        $this->actingAs($oic)->post(route('users.store'), $this->payload(['personnel_id' => $person->id]))
            ->assertSessionHasErrors('personnel_id');
    }

    public function test_email_must_be_unique(): void
    {
        $oic = $this->actor(Role::Oic);
        User::factory()->create(['email' => 'juan@example.com']);

        $this->actingAs($oic)->post(route('users.store'), $this->payload())
            ->assertSessionHasErrors('email');
    }

    public function test_position_is_required_when_creating_a_new_staff_record(): void
    {
        $oic = $this->actor(Role::Oic);

        $this->actingAs($oic)->post(route('users.store'), $this->payload(['position' => '']))
            ->assertSessionHasErrors('position');
    }

    public function test_password_must_be_at_least_eight_characters(): void
    {
        $oic = $this->actor(Role::Oic);

        $this->actingAs($oic)->post(route('users.store'), $this->payload(['password' => 'short']))
            ->assertSessionHasErrors('password');
    }
}
