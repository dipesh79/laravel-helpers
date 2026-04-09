<?php

namespace Dipesh79\LaravelHelpers\Traits;

use Dipesh79\LaravelHelpers\Exception\FilterableColumnsNotSpecifiedException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait Filterable
 *
 * This trait provides a method to filter query results based on specified columns.
 */
trait Filterable
{
    /**
     * Applies a custom filter to the query based on the specified filterable columns.
     *
     * @param Builder $query The query builder instance.
     * @param string $filterQuery The filter query string.
     * @param array $columns The columns to filter on.
     *
     * @return Builder The modified query builder instance.
     * @throws FilterableColumnsNotSpecifiedException If no filterable columns are specified.
     *
     */
    public function scopeCustomFilter(Builder $query, string $filterQuery, array $columns = []): Builder
    {
        $filterableColumns = $columns ?: $this->filterable;

        if (empty($filterableColumns)) {
            throw new FilterableColumnsNotSpecifiedException();
        }

        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

        $query->where(function ($query) use ($filterableColumns, $filterQuery, $operator) {
            foreach ($filterableColumns as $column) {
                if (str_contains($column, '.')) {
                    // Handle nested relationships dynamically
                    $relations = explode('.', $column);
                    // Get the last element as the actual column
                    $finalColumn = array_pop($relations);
                    $query->orWhereHas(implode('.', $relations),
                        function ($q) use ($finalColumn, $filterQuery, $operator) {
                            $q->where($finalColumn, $operator, '%' . $filterQuery . '%');
                        });
                } else {
                    // Handle direct table columns
                    $query->orWhere($column, $operator, '%' . $filterQuery . '%');
                }
            }
        });

        return $query;
    }


}
