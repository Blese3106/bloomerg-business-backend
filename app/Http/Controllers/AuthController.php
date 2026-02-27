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
            'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'phone.required'    => 'Le numéro de téléphone est obligatoire.',
            'phone.unique'      => 'Ce numéro de téléphone est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'wallet'   => 0,
        ]);

        $token = $user->createToken('bloomberg-token')->plainTextToken;

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => [
                'id'     => $user->id,
                'phone'  => $user->phone,
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
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Numéro de téléphone ou mot de passe incorrect.'],
            ]);
        }

        // Revoke old tokens for single session
        $user->tokens()->delete();

        $token = $user->createToken('bloomberg-token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => [
                'id'     => $user->id,
                'phone'  => $user->phone,
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
            'phone'  => $user->phone,
            'wallet' => $user->wallet,
        ]);
    }
}