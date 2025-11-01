<?php

namespace App\Pipeline;

use Illuminate\Database\Eloquent\Builder;
use Closure;

class SortPipe implements QueryPipeline
{
    public function __construct(private ?string $sort) {}

    public function handle(Builder $query, Closure $next): mixed
    {
        if ($this->sort) {
            $direction = 'asc';
            $column = $this->sort;

            if (str_starts_with($this->sort, '-')) {
                $direction = 'desc';
                $column = substr($this->sort, 1);
            }

            $query->orderBy($column, $direction);
        }

        return $next($query);
    }
}
