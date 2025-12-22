<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_path',
        'content',
        'signed_at',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
