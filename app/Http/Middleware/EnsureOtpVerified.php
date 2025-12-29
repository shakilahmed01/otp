<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (is_null($user->otp_verified_at)) {
            // For web: redirect to OTP verification page
            if ($request->wantsJson()) {
                return response()->json(['message' => 'OTP verification required.'], 403);
            }

            return redirect()->route('otp.verify.form');
        }

        return $next($request);
    }
}
