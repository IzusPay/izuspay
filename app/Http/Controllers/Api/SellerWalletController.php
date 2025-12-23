<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerWalletController extends Controller
{
    public function gestao(Request $request): JsonResponse
    {
        $association = $request->user()->association;
        $wallet = Wallet::firstOrCreate(['association_id' => $association->id], ['balance' => 0]);
        $balanceDetails = $association->balanceDetails;

        return response()->json([
            'status' => true,
            'data' => [
                'id' => (string) $wallet->id,
                'sellerId' => (string) $association->id,
                'balance' => (float) ($wallet->balance ?? 0),
                'blockedBalance' => (float) (($balanceDetails['pending_withdrawal'] ?? 0) + ($balanceDetails['retained'] ?? 0)),
                'createdAt' => $wallet->created_at ? $wallet->created_at->toIso8601String() : null,
                'updatedAt' => $wallet->updated_at ? $wallet->updated_at->toIso8601String() : null,
            ],
        ]);
    }
}

