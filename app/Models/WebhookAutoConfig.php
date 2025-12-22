<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookAutoConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope',
        'association_id',
        'skip_every_n_sales',
        'revenue_threshold_cents',
        'reserve_amount_cents',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'skip_every_n_sales' => 'integer',
        'revenue_threshold_cents' => 'integer',
        'reserve_amount_cents' => 'integer',
    ];
}
