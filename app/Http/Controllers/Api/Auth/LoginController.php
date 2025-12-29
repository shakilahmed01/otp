<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendOtpNotification;
use Carbon\Carbon;

class LoginController extends Controller
{
    /**
     * Handle an API login request.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        // If user not OTP verified, generate and send a fresh OTP
        if (is_null($user->otp_verified_at)) {
            $code = (string) random_int(100000, 999999);

            Otp::create([
                'user_id' => $user->id,
                'channel' => 'email',
                'target' => $user->email,
                'token' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]);

            Notification::send($user, new SendOtpNotification($code, 'email'));

            return response()->json([
                'message' => 'Login successful. OTP sent for verification.',
                'token' => $token,
                'user' => $user,
                'otp_required' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
            'otp_required' => false,
        ], 200);
    }
}
