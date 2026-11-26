<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_email(): void
    {
        User::factory()->create(['email' => 'jane@example.com', 'password' => 'secret-pass-1']);

        // Typed with capitals and spaces: still matches.
        $this->post(route('login.attempt'), ['email' => ' Jane@Example.com ', 'password' => 'secret-pass-1'])
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticated();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'jane@example.com', 'password' => 'secret-pass-1']);

        $this->post(route('login.attempt'), ['email' => 'jane@example.com', 'password' => 'nope'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_account_page_requires_login(): void
    {
        $this->get(route('account.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_change_email(): void
    {
        $user = User::factory()->create(['email' => 'old@rafis.test', 'password' => 'secret-pass-1']);

        $this->actingAs($user)->patch(route('account.email.update'), [
            'email' => 'New.Name@Example.com',
            'current_password' => 'secret-pass-1',
        ])->assertRedirect(route('account.edit'))->assertSessionHas('success');

        $this->assertSame('new.name@example.com', $user->fresh()->email);
    }

    public function test_email_change_needs_correct_password(): void
    {
        $user = User::factory()->create(['email' => 'old@rafis.test', 'password' => 'secret-pass-1']);

        $this->actingAs($user)->patch(route('account.email.update'), [
            'email' => 'new@example.com',
            'current_password' => 'wrong',
        ])->assertSessionHasErrors('current_password');

        $this->assertSame('old@rafis.test', $user->fresh()->email);
    }

    public function test_email_must_be_valid_and_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['password' => 'secret-pass-1']);

        $this->actingAs($user)->patch(route('account.email.update'), [
            'email' => 'taken@example.com',
            'current_password' => 'secret-pass-1',
        ])->assertSessionHasErrors('email');

        $this->actingAs($user)->patch(route('account.email.update'), [
            'email' => 'not-an-email',
            'current_password' => 'secret-pass-1',
        ])->assertSessionHasErrors('email');
    }

    public function test_keeping_your_own_email_is_not_a_duplicate_error(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com', 'password' => 'secret-pass-1']);

        $this->actingAs($user)->patch(route('account.email.update'), [
            'email' => 'me@example.com',
            'current_password' => 'secret-pass-1',
        ])->assertSessionHasNoErrors();
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'old-password-1',
            'password' => 'new-password-9',
            'password_confirmation' => 'new-password-9',
        ])->assertRedirect(route('account.edit'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password-9', $user->fresh()->password));
    }

    public function test_password_change_needs_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'wrong',
            'password' => 'new-password-9',
            'password_confirmation' => 'new-password-9',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password-1', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_and_long_enough(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'old-password-1',
            'password' => 'new-password-9',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'old-password-1',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }
}
