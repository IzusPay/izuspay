<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\PerfilModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Exibe a lista de usuários e o formulário de criação.
     */
    public function index()
    {
        $users = User::with('perfis')->paginate(10);
        $perfis = PerfilModel::all();

        return view('associacao.users.index', compact('users', 'perfis'));
    }

    /**
     * Exibe o formulário para criar um novo usuário.
     */
    public function create()
    {
        $perfis = PerfilModel::all();

        return view('associacao.users.create_edit', compact('perfis'));
    }

    /**
     * Armazena um novo usuário no banco de dados.
     */
    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'telefone' => $request->telefone,
        ]);

        $user->perfis()->attach($request->role);

        return redirect()->route('associacao.users.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Exibe o formulário para editar um usuário específico.
     */
    public function edit(User $user)
    {
        $perfis = PerfilModel::all();

        // O `user->load('perfis')` já é feito pelo Laravel ao resolver o modelo
        return view('associacao.users.create_edit', compact('user', 'perfis'));
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(Request $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
            'telefone' => $request->telefone,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        $user->perfis()->sync($request->role);

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove o usuário.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('associacao.users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
