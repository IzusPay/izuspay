<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected $request;

    protected $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->fields() as $field => $value) {
            if (method_exists($this, $field)) {
                call_user_func_array([$this, $field], (array) $value);
            }
        }

        return $this->builder;
    }

    protected function fields(): array
    {
        return array_filter($this->request->all(), function ($value, $key) {
            return method_exists($this, $key) && ! is_null($value);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
