<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;

class FinancialController extends Controller
{
    public function index()
    {

        return view('cliente.financial');
    }
}
