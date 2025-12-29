<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendOtpNotification;
use App\Models\User;

class ApiRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_and_receive_token_and_otp()
    {
        Notification::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email'], 'token']);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('otps', ['user_id' => $user->id]);

        Notification::assertSentTo($user, SendOtpNotification::class);
    }
}
