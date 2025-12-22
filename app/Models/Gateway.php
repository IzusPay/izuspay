<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gateway extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'is_active',
        'credentials_schema',
        'card_fee_percentage',
        'pix_fee_percentage',
        'fixed_fee',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'credentials_schema' => 'array', // <-- A MÁGICA ACONTECE AQUI
    ];

    /**
     * Define o relacionamento com as carteiras (Wallets) que utilizam este gateway.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }
}
