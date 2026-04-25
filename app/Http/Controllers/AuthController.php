<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required'    => 'Le mail est obligatoire.',
            'email.unique'      => 'Ce mail est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'wallet'   => 0,
        ]);

        $token = $user->createToken('bloomberg-token')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => [
                'id'     => $user->id,
                'email'  => $user->email,
                'wallet' => $user->wallet,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect.'],
            ]);
        }

        // Revoke old tokens for single session
        $user->tokens()->delete();

        $token = $user->createToken('bloomberg-token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => [
                'id'     => $user->id,
                'email'  => $user->email,
                'wallet' => $user->wallet,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'     => $user->id,
            'email'  => $user->email,
            'wallet' => $user->wallet,
        ]);
    }
}