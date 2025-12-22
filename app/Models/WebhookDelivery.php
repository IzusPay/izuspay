<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'association_id',
        'endpoint_url',
        'endpoint_description',
        'event',
        'status',
        'is_manual',
        'response_status',
        'payload',
        'error_message',
        'moderation_reason',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_status' => 'integer',
        'is_manual' => 'boolean',
    ];
}
