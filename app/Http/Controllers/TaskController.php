<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    private const GAIN_PAR_TACHE = 5.00;

    /**
     * Generate a new task with two unique references
     */
    public function getTask(Request $request)
    {
        // Generate two unique refs for the current session
        do {
            $ref1 = 'REF' . rand(1000, 9999);
            $ref2 = 'REF' . rand(1000, 9999);
        } while ($ref1 === $ref2);

        // Store refs in signed payload so they can't be tampered
        $payload = [
            'ref1'      => $ref1,
            'ref2'      => $ref2,
            'user_id'   => $request->user()->id,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ];

        // Encode as signed token (HMAC)
        $data    = base64_encode(json_encode($payload));
        $sig     = hash_hmac('sha256', $data, config('app.key'));
        $taskToken = $data . '.' . $sig;

        return response()->json([
            'ref1'       => $ref1,
            'ref2'       => $ref2,
            'task_token' => $taskToken,
            'gain'       => self::GAIN_PAR_TACHE,
        ]);
    }

    /**
     * Validate submitted references and credit wallet
     */
    public function validateTask(Request $request)
    {
        $request->validate([
            'ref1'       => ['required', 'string'],
            'ref2'       => ['required', 'string'],
            'task_token' => ['required', 'string'],
        ]);

        // Verify task token
        [$data, $sig] = array_pad(explode('.', $request->task_token, 2), 2, '');
        $expectedSig = hash_hmac('sha256', $data, config('app.key'));

        if (! hash_equals($expectedSig, $sig)) {
            return response()->json([
                'message' => 'Token de tâche invalide.',
            ], 422);
        }

        $payload = json_decode(base64_decode($data), true);

        // Check expiry
        if (now()->timestamp > ($payload['expires_at'] ?? 0)) {
            return response()->json([
                'message' => 'La tâche a expiré. Veuillez en charger une nouvelle.',
            ], 422);
        }

        // Check user matches
        if ($payload['user_id'] !== $request->user()->id) {
            return response()->json([
                'message' => 'Token de tâche invalide.',
            ], 422);
        }

        // Check refs (case-insensitive)
        $ref1Match = strtoupper($request->ref1) === strtoupper($payload['ref1']);
        $ref2Match = strtoupper($request->ref2) === strtoupper($payload['ref2']);

        if (! $ref1Match || ! $ref2Match) {
            return response()->json([
                'message' => 'Références incorrectes. Veuillez réessayer.',
            ], 422);
        }

        // Credit wallet
        $user = $request->user();
        $user->increment('wallet', self::GAIN_PAR_TACHE);
        $user->refresh();

        // Record transaction
        $transaction = Transaction::create([
            'user_id'     => $user->id,
            'tx_id'       => 'TX-' . strtoupper(Str::random(8)),
            'amount'      => self::GAIN_PAR_TACHE,
            'type'        => 'task',
            'description' => 'Tâche validée — Publication d\'article',
        ]);

        return response()->json([
            'message'       => 'Tâche validée ! +$' . self::GAIN_PAR_TACHE . ' ajoutés.',
            'wallet'        => $user->wallet,
            'transaction'   => [
                'id'     => $transaction->id,
                'tx_id'  => $transaction->tx_id,
                'amount' => $transaction->amount,
                'date'   => $transaction->created_at->format('d/m/Y'),
            ],
        ]);
    }
}