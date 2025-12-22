<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Models\Fee;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalApiController extends Controller
{
    public function index(Request $request)
    {
        $associationId = $request->user()->association_id;
        $withdrawals = Withdrawal::whereHas('wallet', function ($q) use ($associationId) {
            $q->where('association_id', $associationId);
        })->latest()->paginate(15);

        return response()->json([
            'data' => $withdrawals->items(),
            'meta' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'per_page' => $withdrawals->perPage(),
                'total' => $withdrawals->total(),
            ],
        ]);
    }

    public function show(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->wallet->association_id !== $request->user()->association_id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $feeConfig = Fee::where('association_id', $withdrawal->wallet->association_id)
            ->where('payment_method', 'withdrawal')
            ->first();
        $percentage = (float) ($feeConfig->percentage_fee ?? 0);
        $fixed = (float) ($feeConfig->fixed_fee ?? 5.00);
        $feeAmount = ($withdrawal->amount * ($percentage / 100)) + $fixed;

        return response()->json([
            'id' => $withdrawal->id,
            'amount' => $withdrawal->amount,
            'fee_amount' => $feeAmount,
            'net_amount' => max(0, $withdrawal->amount - $feeAmount),
            'status' => $withdrawal->status,
            'pix_key' => $withdrawal->pix_key,
            'pix_key_type' => $withdrawal->pix_key_type,
            'created_at' => $withdrawal->created_at,
        ]);
    }

    public function store(WithdrawalRequest $request)
    {
        $wallet = Wallet::where('association_id', $request->user()->association_id)->first();
        if (! $wallet) {
            return response()->json(['message' => 'Carteira não encontrada'], 404);
        }

        $withdrawalAmount = (float) $request->amount;
        $minWithdrawal = 10.00;
        $feeConfig = Fee::where('association_id', $wallet->association_id)
            ->where('payment_method', 'withdrawal')
            ->first();
        $percentage = (float) ($feeConfig->percentage_fee ?? 0);
        $fixed = (float) ($feeConfig->fixed_fee ?? 5.00);
        $withdrawalFee = ($withdrawalAmount * ($percentage / 100)) + $fixed;

        if ($withdrawalAmount < $minWithdrawal) {
            return response()->json(['message' => 'O valor mínimo para saque é de R$ 10,00.'], 422);
        }

        $amountToDeduct = $withdrawalAmount + $withdrawalFee;
        $available = optional($request->user()->association)->balanceDetails['available'] ?? 0;
        if ($amountToDeduct > $available) {
            return response()->json(['message' => 'Saldo insuficiente para cobrir o valor do saque e a taxa.'], 422);
        }

        $wallet->balance -= $amountToDeduct;
        $wallet->save();

        $data = [
            'wallet_id' => $wallet->id,
            'amount' => $withdrawalAmount,
            'status' => 'pending',
        ];
        if ($request->filled('bank_account_id')) {
            $data['bank_account_id'] = $request->bank_account_id;
        } else {
            $data['pix_key'] = $request->pix_key;
            $data['pix_key_type'] = $request->pix_key_type;
        }

        $withdrawal = Withdrawal::create($data);

        return response()->json([
            'id' => $withdrawal->id,
            'amount' => $withdrawal->amount,
            'fee_amount' => $withdrawalFee,
            'net_amount' => max(0, $withdrawal->amount - $withdrawalFee),
            'status' => $withdrawal->status,
            'pix_key' => $withdrawal->pix_key,
            'pix_key_type' => $withdrawal->pix_key_type,
            'created_at' => $withdrawal->created_at,
        ], 201);
    }
}
