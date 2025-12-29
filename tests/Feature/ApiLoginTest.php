<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendOtpNotification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ApiLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_sends_otp_when_user_not_verified()
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'otp_verified_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['otp_required' => true])
                 ->assertJsonStructure(['message', 'token', 'user']);

        Notification::assertSentTo($user, SendOtpNotification::class);
        $this->assertDatabaseHas('otps', ['user_id' => $user->id]);
    }

    public function test_login_returns_401_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_verified_user_login_does_not_send_otp()
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'otp_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['otp_required' => false]);

        Notification::assertNothingSent();
    }
}
