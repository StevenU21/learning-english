<?php

namespace App\Classes;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CollectionHelper
{
    /**
     * Transforms a paginated collection using the given callback.
     *
     * @param  LengthAwarePaginator  $paginator
     * @return LengthAwarePaginator
     */
    public static function transformPaginated($paginator, callable $callback)
    {
        $paginator->getCollection()->transform($callback);

        return $paginator;
    }

    /**
     * Transforms a collection using the given callback.
     *
     * @param  Collection  $collection
     * @return Collection
     */
    public static function transform($collection, callable $callback)
    {
        return $collection->map($callback);
    }
}
