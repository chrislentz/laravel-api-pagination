<?php

namespace ChrisLentz\LaravelApiPagination;

use ArrayAccess;
use Countable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Request;
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

        $this->buildUrl();
        $this->generateProperties();
        
        if (count($this->items) > $this->limit) {
            $last_key = $this->items->keys()->last();
            $this->items = $this->items->forget($last_key);
        }
    }

    protected function buildUrl()
    {
        $this->url = Request::url() . '?';

        foreach (Request::all() as $k => $v) {
            if (!in_array($k, ['_limit', '_offset'])) {
                $this->url .= $k . '=' . $v . '&';
                $has_param = true;
            }
        }
    }

    protected function generateProperties()
    {
        $previous_limit = ($this->offset >= $this->limit) ? $this->limit : $this->offset;

        // Generate next offset & page
        if (count($this->items) > $this->limit) {
            $this->next_offset = $this->offset + $this->limit;
            $this->next_page = $this->url . '_limit=' . $this->limit . '&_offset=' . $this->next_offset;
        } else {
            $this->next_offset = null;
            $this->next_page = null;
        }

        // Generate previous offset & page
        if ($this->offset > 0) {
            $this->previous_offset = max(0, $this->offset - $this->limit);
            $this->previous_page = $this->url . '_limit=' . $this->limit . '&_offset=' . $this->previous_offset;
        } else {
            $this->previous_offset = null;
            $this->previous_page = null;
        }
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getNextOffset()
    {
        return $this->next_offset;
    }

    public function getNextPage()
    {
        return $this->next_page;
    }

    public function getPreviousOffset()
    {
        return $this->previous_offset;
    }

    public function getPreviousPage()
    {
        return $this->previous_page;
    }

    public function toArray()
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset,
            'next_offset' => $this->next_offset,
            'next_page' => $this->next_page,
            'previous_offset' => $this->previous_offset,
            'previous_page' => $this->previous_page,
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