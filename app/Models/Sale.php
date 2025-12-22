<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // Importar Builder para escopos

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'association_id',
        'plan_id',
        'product_id',
        'user_id',
        'total_price',
        'payment_method',
        'status',
        'transaction_hash',
        'fee_percentage',
        'fee_fixed',
        'fee_total',
        'net_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'fee_percentage' => 'decimal:4',
        'fee_fixed' => 'decimal:2',
        'fee_total' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    /**
     * Get the association that owns the Sale.
     */
    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    /**
     * Get the plan that owns the Sale.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the user that owns the Sale.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that owns the Sale.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the transactions for the Sale.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'paid' => '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">Pago</span>',
            'awaiting_payment' => '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">Pendente</span>',
            'cancelled' => '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">Cancelado</span>',
            'refunded' => '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">Reembolsado</span>',
            'chargeback' => '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400">Chargeback</span>',
            'refused' => '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">Recusado</span>',
            default => '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400">Indefinido</span>'
        };
    }

    /**
     * Scope a query to only include sales within a specific date range.
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): void
    {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include sales with a specific status.
     */
    public function scopeWhereStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope a query to only include sales by a specific user.
     */
    public function scopeWhereUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include sales related to a specific association.
     */
    public function scopeWhereAssociation(Builder $query, int $associationId): void
    {
        $query->where('association_id', $associationId);
    }

    /**
     * Scope a query to only include sales related to a specific plan.
     */
    public function scopeWherePlan(Builder $query, int $planId): void
    {
        $query->where('plan_id', $planId);
    }

    /**
     * Scope a query to only include sales related to a specific product.
     */
    public function scopeWhereProduct(Builder $query, int $productId): void
    {
        $query->where('product_id', $productId);
    }

    /**
     * Scope a query to include sales with user, product, and plan relationships.
     */
    public function scopeWithRelations(Builder $query): void
    {
        $query->with(['user', 'product', 'plan']);
    }

    /**
     * Scope a query to order sales by creation date in descending order.
     */
    public function scopeLatest(Builder $query): void
    {
        $query->orderBy('created_at', 'DESC');
    }

    /**
     * Scope a query to get sales for a dashboard chart, typically paid sales grouped by date.
     */
    public function scopeForDailyChart(Builder $query): void
    {
        $query->where('status', 'paid')
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC');
    }
}
