<?php

namespace App\Services;

use App\Models\PerfilModel;

class PerfilService
{
    public function getAllPerfis()
    {
        return PerfilModel::all();
    }

    public function createPerfil(array $data)
    {
        return PerfilModel::create($data);
    }

    public function getPerfilById($id)
    {
        return PerfilModel::findOrFail($id);
    }

    public function updatePerfil($id, array $data)
    {
        $perfil = PerfilModel::findOrFail($id);
        $perfil->update($data);

        return $perfil;
    }

    public function deletePerfil($id)
    {
        $perfil = PerfilModel::findOrFail($id);
        $perfil->delete();
    }
}
