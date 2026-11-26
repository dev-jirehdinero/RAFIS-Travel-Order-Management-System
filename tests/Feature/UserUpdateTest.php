<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function actor(Role $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** A staff account with a linked staff record. */
    private function staffAccount(string $name = 'OLD, NAME'): User
    {
        $person = Personnel::factory()->create(['name' => $name]);

        return User::factory()->create([
            'name' => $name,
            'role' => Role::Staff,
            'personnel_id' => $person->id,
            'password' => 'old-password-1',
        ]);
    }

    // ---- who may edit ----------------------------------------------------

    public function test_staff_cannot_edit_or_reset_anyone(): void
    {
        $staff = $this->actor(Role::Staff);
        $target = $this->staffAccount();

        $this->actingAs($staff)->get(route('users.edit', $target))->assertForbidden();
        $this->actingAs($staff)->patch(route('users.update', $target), ['name' => 'X', 'role' => 'staff'])->assertForbidden();
        $this->actingAs($staff)->put(route('users.password', $target), ['password' => 'new-password-9'])->assertForbidden();
    }

    public function test_oic_cannot_edit_oic_or_admin_accounts(): void
    {
        $oic = $this->actor(Role::Oic);

        foreach ([$this->actor(Role::Oic), $this->actor(Role::Admin)] as $target) {
            $this->actingAs($oic)->get(route('users.edit', $target))->assertForbidden();
            $this->actingAs($oic)->put(route('users.password', $target), ['password' => 'new-password-9'])->assertForbidden();
        }
    }

    // ---- name + role -----------------------------------------------------

    public function test_oic_can_rename_a_staff_account_and_the_staff_record_follows(): void
    {
        $oic = $this->actor(Role::Oic);
        $target = $this->staffAccount('DELA CRUZ, JUAN');

        $this->actingAs($oic)->patch(route('users.update', $target), [
            'name' => 'DELA CRUZ, JUAN P.',
            'role' => 'staff',
        ])->assertRedirect(route('users.index'))->assertSessionHas('success');

        $this->assertSame('DELA CRUZ, JUAN P.', $target->fresh()->name);
        $this->assertSame('DELA CRUZ, JUAN P.', $target->fresh()->personnel->name);
    }

    public function test_oic_cannot_promote_anyone(): void
    {
        $oic = $this->actor(Role::Oic);
        $target = $this->staffAccount();

        foreach (['oic', 'admin'] as $role) {
            $this->actingAs($oic)->patch(route('users.update', $target), ['name' => 'OLD, NAME', 'role' => $role])
                ->assertSessionHasErrors('role');
        }

        $this->assertSame(Role::Staff, $target->fresh()->role);
    }

    public function test_admin_can_change_a_role(): void
    {
        $admin = $this->actor(Role::Admin);
        $target = $this->staffAccount();

        $this->actingAs($admin)->patch(route('users.update', $target), ['name' => 'OLD, NAME', 'role' => 'oic'])
            ->assertRedirect(route('users.index'));

        $this->assertSame(Role::Oic, $target->fresh()->role);
    }

    public function test_nobody_can_change_their_own_role_but_can_rename_themselves(): void
    {
        $admin = $this->actor(Role::Admin);

        $this->actingAs($admin)->patch(route('users.update', $admin), ['name' => $admin->name, 'role' => 'staff'])
            ->assertSessionHasErrors('role');
        $this->assertSame(Role::Admin, $admin->fresh()->role);

        $this->actingAs($admin)->patch(route('users.update', $admin), ['name' => 'Renamed Admin', 'role' => 'admin'])
            ->assertRedirect(route('users.index'));
        $this->assertSame('Renamed Admin', $admin->fresh()->name);
    }

    public function test_name_cannot_clash_with_another_staff_record(): void
    {
        $admin = $this->actor(Role::Admin);
        Personnel::factory()->create(['name' => 'TAKEN, NAME']);
        $target = $this->staffAccount();

        $this->actingAs($admin)->patch(route('users.update', $target), ['name' => 'TAKEN, NAME', 'role' => 'staff'])
            ->assertSessionHasErrors('name');
    }

    public function test_turning_an_admin_into_staff_needs_a_position_and_creates_a_record(): void
    {
        $admin = $this->actor(Role::Admin);
        $other = $this->actor(Role::Admin); // has no staff record

        $this->actingAs($admin)->patch(route('users.update', $other), ['name' => 'NEW, STAFF', 'role' => 'staff'])
            ->assertSessionHasErrors('position');

        $this->actingAs($admin)->patch(route('users.update', $other), [
            'name' => 'NEW, STAFF',
            'role' => 'staff',
            'position' => 'Administrative Aide VI',
        ])->assertRedirect(route('users.index'));

        $other->refresh();
        $this->assertSame(Role::Staff, $other->role);
        $this->assertNotNull($other->personnel_id);
        $this->assertDatabaseHas('personnel', ['id' => $other->personnel_id, 'position' => 'Administrative Aide VI']);
    }

    // ---- reset password --------------------------------------------------

    public function test_oic_can_reset_a_staff_password(): void
    {
        $oic = $this->actor(Role::Oic);
        $target = $this->staffAccount();

        $this->actingAs($oic)->put(route('users.password', $target), ['password' => 'temp-pass-77'])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('temp-pass-77', $target->fresh()->password));
        $this->assertFalse(Hash::check('old-password-1', $target->fresh()->password));
    }

    public function test_reset_password_must_be_at_least_eight_characters(): void
    {
        $oic = $this->actor(Role::Oic);
        $target = $this->staffAccount();

        $this->actingAs($oic)->put(route('users.password', $target), ['password' => 'short'])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password-1', $target->fresh()->password));
    }

    public function test_admin_can_reset_an_oic_password(): void
    {
        $admin = $this->actor(Role::Admin);
        $oic = $this->actor(Role::Oic);

        $this->actingAs($admin)->put(route('users.password', $oic), ['password' => 'temp-pass-77'])
            ->assertRedirect(route('users.index'));

        $this->assertTrue(Hash::check('temp-pass-77', $oic->fresh()->password));
    }
}
