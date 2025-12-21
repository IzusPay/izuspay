<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputePercentage = 0.0;
        $activeTab = 'disputes';
        $search = $request->get('search', '');

        return view('associacao.disputas.index', compact('disputePercentage', 'activeTab', 'search'));
    }
}
