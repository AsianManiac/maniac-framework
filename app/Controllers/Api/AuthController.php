<?php

namespace App\Controllers\Api;

use Core\Mvc\Controller;
use Core\Http\Request;
use Core\Hashing\Hash;
use App\Models\User;
use Core\Auth\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        // Basic validation (implement a Validator)
        $credentials = $request->only(['email', 'password']);

        if (empty($credentials['email']) || empty($credentials['password'])) {
            return response()->json(['message' => 'Email and password required'], 422);
        }

        $user = User::where('email', '=', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // --- Issue Token ---
        // Generate secure plain text token
        $plainTextToken = bin2hex(random_bytes(32)); // 64 chars hex
        // Hash it for storage
        $hashedToken = hash('sha256', $plainTextToken);

        // Update user's token hash in DB
        $user->api_token = $hashedToken;
        if (!$user->save()) {
            // Log error
            return response()->json(['message' => 'Failed to update token'], 500);
        }

        // --- Return plain text token to the user ONCE ---
        return response()->json([
            'message' => 'Login successful',
            'user' => new \App\Http\Resources\UserResource($user), // Return user data
            'token' => $plainTextToken, // The user needs this plain token
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        // Get authenticated user (via middleware)
        $user = Auth::user();
        if ($user) {
            // Clear the token hash from the database
            $user->api_token = null;
            $user->save();
            return response()->json(['message' => 'Logged out successfully']);
        }
        return response()->json(['message' => 'No authenticated user'], 401);
    }
}
