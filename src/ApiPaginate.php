<?php

namespace ChrisLentz\LaravelApiPagination;

use ArrayAccess;
use Countable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;
use IteratorAggregate;
use JsonSerializable;

class ApiPaginate extends AbstractPaginator implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    protected $limit;
    protected $offset;
    protected $next_offset;
    protected $previous_offset;
    protected $next_page;
    protected $previous_page;

    public function __construct($items, int $limit = 5, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->generateProperties();
        
        $this->items->forget($limit);
    }

    protected function generateProperties()
    {
        $previous_limit = ($this->offset >= $this->limit) ? $this->limit : $this->offset;

        // previous_page
        if ($this->offset > 0) {
            $this->previous_page = 'limit=' . $previous_limit . '&offset=' . max(0, $this->offset - $this->limit);
        } else {
            $this->previous_page = null;
        }

        // next_page
        if (count($this->items) > $this->limit) {
            $this->next_page = 'limit=' . $this->limit . '&offset=' . ($this->offset + $this->limit);
        } else {
            $this->next_page = null;
        }
    }

    public function nextPage()
    {
        return $this->next_page;
    }

    public function previousPage()
    {
        return $this->previous_page;
    }

    public function toArray()
    {
        return [
            'previous_page' => $this->previous_page,
            'next_page' => $this->next_page,
            'data' => $this->items->toArray(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
