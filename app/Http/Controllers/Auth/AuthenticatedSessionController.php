<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            $token = $user->createToken('Lht_SR_app_accesstoken'.$user->id)->plainTextToken;

            return response()->json([
                'message' => 'Login successfully.',
                'user' => $user,
                'access_token' => $token,
            ]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);

    }

    /**
     * Destroy an authenticated access token.
     */
    public function destroy(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
