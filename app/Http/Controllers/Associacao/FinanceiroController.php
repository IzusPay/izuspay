<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\BankAccount;
use App\Models\Fee;
use App\Models\Sale;
use App\Models\Wallet;
use App\Models\Withdrawal;

class FinanceiroController extends Controller
{
    public function index()
    {
        $associationId = request()->user()->association_id;

        $wallet = Wallet::firstOrCreate(['association_id' => $associationId], ['balance' => 0]);
        $association = Association::find($associationId);
        $balanceDetails = $association ? $association->balanceDetails : [
            'available' => 0,
            'total_gross' => 0,
            'total_withdrawn' => 0,
            'pending_release' => 0,
            'retained' => 0,
            'pending_withdrawal' => 0,
            'last_update' => now()->diffForHumans(),
        ];

        $totalRevenue = Sale::where('association_id', $associationId)->where('status', 'paid')->sum('total_price');
        $pendingRevenue = Sale::where('association_id', $associationId)->where('status', 'awaiting_payment')->sum('total_price');

        $totalWithdrawals = Withdrawal::whereHas('wallet', function ($q) use ($associationId) {
            $q->where('association_id', $associationId);
        })->where('status', 'completed')->sum('amount');

        $pendingWithdrawals = Withdrawal::whereHas('wallet', function ($q) use ($associationId) {
            $q->where('association_id', $associationId);
        })->where('status', 'pending')->sum('amount');

        $recentSales = Sale::where('association_id', $associationId)->with(['user', 'plan'])->latest()->take(5)->get();

        $fees = Fee::where('association_id', $associationId)->get();

        $bankAccounts = BankAccount::where('association_id', $associationId)->get();
        $withdrawals = Withdrawal::whereHas('wallet', function ($q) use ($associationId) {
            $q->where('association_id', $associationId);
        })->with('bankAccount')->latest()->paginate(10);

        return view('associacao.financeiro.index', compact(
            'wallet',
            'balanceDetails',
            'totalRevenue',
            'pendingRevenue',
            'totalWithdrawals',
            'pendingWithdrawals',
            'recentSales',
            'bankAccounts',
            'withdrawals',
            'fees'
        ));
    }
}
