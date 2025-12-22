<?php

namespace App\Http\Controllers;

use App\Models\PerfilModel;
use App\Models\User;
use App\Models\UserPerfilModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);  // Obtém todos os usuários
        $perfis = PerfilModel::all();

        return view('users.index', compact('users', 'perfis'));  // Exibe a lista de usuários
    }

    public function create()
    {
        $perfis = PerfilModel::all();

        return view('users.create', compact('perfis'));  // Exibe o formulário para criar um novo usuário
    }

    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        UserPerfilModel::create([
            'user_id' => $user->id,
            'perfil_id' => $request['perfil_id'],
            'is_atual' => 1, // Ou qualquer outro valor que você deseja para "is_atual"
            'status' => 1, // Ou outro valor que você deseja para "status"
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));  // Exibe o formulário para editar um usuário
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        $user->delete();  // Exclui o usuário

        return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
