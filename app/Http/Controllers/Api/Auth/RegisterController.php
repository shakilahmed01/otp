<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Handle an API registration request.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Fire the Registered event so the OTP listener runs
        event(new Registered($user));

        // create an API token (Sanctum)
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'User registered. OTP sent to your email.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}
