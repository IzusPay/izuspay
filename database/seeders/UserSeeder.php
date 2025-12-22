<?php

namespace Database\Seeders;

use App\Models\PerfilModel;
use App\Models\User;
use App\Models\UserPerfilModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Criação do usuário
        $user = User::create([
            'name' => 'Administrador Geral',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456789'), // Substitua por uma senha segura
        ]);

        // Criação dos perfis
        $perfilAdmin = PerfilModel::create(['name' => 'Administrador']);
        $perfilCliente = PerfilModel::create(['name' => 'Associacao']);
        $perfilCliente = PerfilModel::create(['name' => 'Cliente']);

        // Associação do usuário com o perfil "Administrador"
        UserPerfilModel::create([
            'user_id' => $user->id,
            'perfil_id' => $perfilAdmin->id,
            'is_atual' => 1, // Define como perfil atual
            'status' => 1,   // Ativo
        ]);
    }
}
