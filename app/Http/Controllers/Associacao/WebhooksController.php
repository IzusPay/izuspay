<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhooksController extends Controller
{
    public function index(Request $request)
    {
        $inactiveCount = 0;
        $activeCount = 1;
        $webhooks = [];

        return view('associacao.webhooks.index', compact('inactiveCount', 'activeCount', 'webhooks'));
    }
}
