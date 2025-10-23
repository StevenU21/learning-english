<?php

namespace App\Classes;

class CollectionHelper
{
    /**
     * Transforms a paginated collection using the given callback.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param callable $callback
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function transformPaginated($paginator, callable $callback)
    {
        $paginator->getCollection()->transform($callback);
        return $paginator;
    }

    /**
     * Transforms a collection using the given callback.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param callable $callback
     * @return \Illuminate\Support\Collection
     */
    public static function transform($collection, callable $callback)
    {
        return $collection->map($callback);
    }
}
