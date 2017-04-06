<?php

namespace ChrisLentz\LaravelApiPagination;

use ChrisLentz\LaravelApiPagination\ApiPaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait ApiPaginateTrait
{
    public function scopeApiPaginate(Builder $query)
    {
        if (Request::has('_limit')) {
            // Limit "_limit" to a max of 100
            (Request::get('_limit') > 100) ? $this->limit = 100 : $this->limit = Request::get('_limit');
        } else {
            $this->limit = 5;
        }

        if (Request::has('_offset')) {
            $this->offset = Request::get('_offset');
        } else {
            $this->offset = 0;
        }

        $query->offset($this->offset)->limit($this->limit + 1);

        $this->items = $query->get();

        $this->pagination = new ApiPaginate($this->items, $this->limit, $this->offset);

        return $this->pagination;
    }
}
