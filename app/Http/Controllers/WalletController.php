<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get wallet balance + transaction history
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = $user->transactions()
            ->select('tx_id', 'amount', 'type', 'description', 'created_at')
            ->paginate(20);

        return response()->json([
            'wallet'       => $user->wallet,
            'transactions' => $transactions->map(fn ($t) => [
                'tx_id'       => $t->tx_id,
                'amount'      => $t->amount,
                'type'        => $t->type,
                'description' => $t->description,
                'date'        => $t->created_at->format('d/m/Y'),
            ]),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }
}