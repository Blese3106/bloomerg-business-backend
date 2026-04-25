<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    private const MIN_WITHDRAWAL = 3000;

    /**
     * Submit a withdrawal request
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check minimum balance
        if ($user->wallet < self::MIN_WITHDRAWAL) {
            return response()->json([
                'message' => 'Solde insuffisant. Le retrait minimum est de $' . self::MIN_WITHDRAWAL . '.',
            ], 422);
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'address'    => ['required', 'string', 'max:500'],
            'city'       => ['required', 'string', 'max:255'],
            'country'    => ['required', 'string', 'max:255'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required'  => 'Le nom est obligatoire.',
            'email.required'      => 'L\'email est obligatoire.',
            'email.email'         => 'L\'email doit être valide.',
            'address.required'    => 'L\'adresse est obligatoire.',
            'city.required'       => 'La ville est obligatoire.',
            'country.required'    => 'Le pays est obligatoire.',
        ]);

        // Create withdrawal request
        $withdrawal = WithdrawalRequest::create([
            'user_id'    => $user->id,
            'request_id' => 'WD-' . strtoupper(Str::random(8)),
            'amount'     => $user->wallet,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'address'    => $request->address,
            'city'       => $request->city,
            'country'    => $request->country,
            'status'     => 'pending',
        ]);

        return response()->json([
            'message' => 'Demande de retrait soumise avec succès.',
            'withdrawal' => [
                'request_id' => $withdrawal->request_id,
                'amount'     => $withdrawal->amount,
                'status'     => $withdrawal->status,
            ],
        ], 201);
    }

    /**
     * Get user's withdrawal requests
     */
    public function index(Request $request)
    {
        $withdrawals = $request->user()
            ->withdrawalRequests()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($w) => [
                'request_id' => $w->request_id,
                'amount'     => $w->amount,
                'status'     => $w->status,
                'date'       => $w->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json(['withdrawals' => $withdrawals]);
    }
}