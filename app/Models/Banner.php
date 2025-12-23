<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'association_id',
        'name',
        'image_url',
        'link',
        'status',
        'created_by_admin',
    ];

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }
}
