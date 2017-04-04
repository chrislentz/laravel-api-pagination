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

    private function generatePaginationMetaData()
    {
        $meta_data = collect([
            'previous_page' => null,
            'next_page' => null,
        ]);

        $previous_limit = ($this->offset >= $this->limit) ? $this->limit : $this->offset;

        // previous_page
        if ($this->offset > 0) {
            $meta_data['previous_page'] = 'limit=' . $previous_limit . '&offset=' . max(0, $this->offset - $this->limit);
        }

        // next_page
        if (count($this->items) > $this->limit) {
            $meta_data['next_page'] = 'limit=' . $this->limit . '&offset=' . ($this->offset + $this->limit);
        }

        return $meta_data;
    }
}
