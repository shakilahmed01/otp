<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use App\Models\Otp;
use App\Notifications\SendOtpNotification;

class OtpController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showVerifyForm()
    {
        return view('auth.otp_verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $token = $request->input('token');

        $otp = Otp::where('user_id', $user->id)
            ->where('token', $token)
            ->where('used', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $otp) {
            return back()->withErrors(['token' => 'Invalid verification code.']);
        }

        if ($otp->expires_at && Carbon::now()->gt($otp->expires_at)) {
            return back()->withErrors(['token' => 'This verification code has expired.']);
        }

        $otp->used = true;
        $otp->save();

        $user->otp_verified_at = Carbon::now();
        $user->save();

        return redirect()->intended('/home')->with('status', 'OTP verified successfully.');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        $token = (string) random_int(100000, 999999);

        $otp = Otp::create([
            'user_id' => $user->id,
            'channel' => 'email',
            'target' => $user->email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Notification::send($user, new SendOtpNotification($token));

        return back()->with('status', 'A new verification code was sent.');
    }

    // API: verify with JSON response
    public function apiVerify(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $user = $request->user();
        $token = $request->input('token');

        $otp = Otp::where('user_id', $user->id)
            ->where('token', $token)
            ->where('used', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'Invalid verification code.'], 404);
        }

        if ($otp->expires_at && Carbon::now()->gt($otp->expires_at)) {
            return response()->json(['message' => 'This verification code has expired.'], 422);
        }

        $otp->used = true;
        $otp->save();

        $user->otp_verified_at = Carbon::now();
        $user->save();

        return response()->json(['message' => 'OTP verified successfully.', 'user' => $user], 200);
    }

    // API: resend OTP with JSON response
    public function apiResend(Request $request)
    {
        $user = $request->user();

        $token = (string) random_int(100000, 999999);

        $otp = Otp::create([
            'user_id' => $user->id,
            'channel' => 'email',
            'target' => $user->email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Notification::send($user, new SendOtpNotification($token));

        return response()->json(['message' => 'A new verification code was sent.'], 200);
    }
}
