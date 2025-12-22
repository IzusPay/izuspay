<?php

namespace App\Models;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_active',
        'association_id',
        'tipo_produto',
        'entrega_produto',
        'categoria_produto',
        'url_venda',
        'nome_sac',
        'email_sac',
        'image',
        'offer_hash_goat',
        'product_hash_goat',
        'hash_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'tipo_produto' => 'integer',
        'entrega_produto' => 'integer',
        'categoria_produto' => 'integer',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_product');
    }

    // Relacionamento com a associação
    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (! $product->hash_id) {
                $hashids = new Hashids(config('app.key'), 8);
                $product->hash_id = $hashids->encode(time());
            }
        });
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
