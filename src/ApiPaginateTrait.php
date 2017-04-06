<?php

namespace ChrisLentz\LaravelApiPagination;

use ChrisLentz\LaravelApiPagination\ApiPaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait ApiPaginateTrait
{
    public function scopeApiPaginate(Builder $query)
    {
        if (Request::has('limit')) {
            // Limit "limit" to a max of 100
            (Request::get('limit') > 100) ? $this->limit = 100 : $this->limit = Request::get('limit');
        } else {
            $this->limit = 5;
        }

        if (Request::has('offset')) {
            $this->offset = Request::get('offset');
        } else {
            $this->offset = 0;
        }

        $query->offset($this->offset)->limit($this->limit + 1);

        $this->items = $query->get();

        $this->pagination = new ApiPaginate($this->items, $this->limit, $this->offset);

        return $this->pagination;
    }
}
