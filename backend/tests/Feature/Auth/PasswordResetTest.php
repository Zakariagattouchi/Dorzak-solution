<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_link_for_known_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'owner@dorzak.com']);

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'owner@dorzak.com'])
            ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_200_for_unknown_email_without_sending(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@dorzak.com'])
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@dorzak.com',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'owner@dorzak.com',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'owner@dorzak.com', 'password' => Hash::make('old-password')]);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'owner@dorzak.com',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_reset_requires_confirmed_password(): void
    {
        User::factory()->create(['email' => 'owner@dorzak.com']);
        $token = Password::createToken(User::first());

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'owner@dorzak.com',
            'password' => 'brand-new-password',
            'password_confirmation' => 'mismatch',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }
}
