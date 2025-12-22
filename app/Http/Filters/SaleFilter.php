<?php

namespace App\Http\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SaleFilter extends QueryFilter
{
    public function status(?string $status = null)
    {
        if ($status) {
            $this->builder->where('status', $status);
        }
    }

    public function search(?string $search = null)
    {
        if ($search) {
            $this->builder->where(function (Builder $query) use ($search) {
                $query->where('transaction_hash', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function (Builder $q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('product', function (Builder $q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('plan', function (Builder $q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }
    }

    public function fromDate(?string $date = null)
    {
        if ($date) {
            $this->builder->where('created_at', '>=', Carbon::parse($date)->startOfDay());
        }
    }

    public function toDate(?string $date = null)
    {
        if ($date) {
            $this->builder->where('created_at', '<=', Carbon::parse($date)->endOfDay());
        }
    }

    public function userId(?int $userId = null)
    {
        if ($userId) {
            $this->builder->where('user_id', $userId);
        }
    }

    public function associationId(?int $associationId = null)
    {
        if ($associationId) {
            $this->builder->where('association_id', $associationId);
        }
    }

    public function paymentMethod(?string $method = null)
    {
        if ($method) {
            $this->builder->where('payment_method', $method);
        }
    }
}
