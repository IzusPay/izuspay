<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;

class ClienteController extends Controller
{
    /**
     * Exibe o dashboard principal do cliente.
     */
    public function dashboard()
    {
        return view('cliente.dashboard');
    }

    /**
     * Exibe a tela de espera.
     */
    public function wait()
    {
        return view('cliente.wait');
    }
}
