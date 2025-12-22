<?php

namespace App\Models;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'association_id',
        'name',
        'description',
        'recurrence',
        'image',
        'is_active',
        'client_type',
        'hash_id',
        // Adições para integração com Goat Payments e Assinaturas
        'price', // Adicionado: Preço do plano (em centavos, se Goat Payments usar assim)
        'offer_hash', // Adicionado: Hash da oferta na Goat Payments
        'product_hash', // Adicionado: Hash do produto na Goat Payments
        'duration_in_months', // Adicionado: Duração do plano em meses para a assinatura
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer', // Adicionado: Assumindo que o preço será armazenado como inteiro (centavos)
    ];

    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            $hashids = new Hashids(config('app.key'), 8);
            $plan->hash_id = $hashids->encode(time());
        });
    }

    // Define o relacionamento com a associação
    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    // Define o relacionamento com os produtos
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'plan_product');
    }

    // Define o relacionamento com as vendas
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // Acessor para calcular o preço total do plano
    // Mantenho este acessor, mas o campo 'price' no fillable será o valor principal para a Goat
    public function getTotalPriceAttribute(): float
    {
        return $this->products->sum('price');
    }

    /**
     * Accessor para preço em reais (convertendo de centavos)
     */
    public function getPriceInReaisAttribute(): float
    {
        return $this->price / 100;
    }

    /**
     * Accessor para preço formatado em reais
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ '.number_format($this->price_in_reais, 2, ',', '.');
    }

    /**
     * Scope para buscar apenas planos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por preço
     */
    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }
}
