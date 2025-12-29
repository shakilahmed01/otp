<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendOtpNotification;
use App\Models\Otp;
use Carbon\Carbon;

class SendOtpOnRegistered
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Generate 6-digit token
        $token = (string) random_int(100000, 999999);

        $otp = Otp::create([
            'user_id' => $user->id,
            'channel' => 'email',
            'target' => $user->email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send notification (email for now)
        Notification::send($user, new SendOtpNotification($token, 'email'));
    }
}
