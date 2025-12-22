<?php

// app/Models/DashboardSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardSetting extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'layout'];

    protected $casts = [
        'layout' => 'array', // Garante que o campo seja tratado como array/json
    ];
}
