<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_required',
        'is_active',
        'association_id',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function documentations()
    {
        return $this->hasMany(Documentation::class);
    }

    public function association()
    {
        return $this->belongsTo(Association::class);
    }
}
