<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilModel extends Model
{
    use HasFactory;

    protected $table = 'perfis';

    protected $fillable = ['name'];

    public function userPerfis()
    {
        return $this->hasMany(UserPerfilModel::class, 'perfil_id');
    }
}
