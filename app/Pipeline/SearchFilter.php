<?php

namespace App\Pipeline;

use Illuminate\Database\Eloquent\Builder;
use Closure;

class SearchFilter implements QueryPipeline
{

    public function __construct(private ?string $value) {}

    public function handle(Builder $query, Closure $next): mixed
    {
        $query->when($this->value,
        fn($q, $value) => $q->where('code', 'LIKE', '%' . $value . '%'));

        return $next($query);
    }
}
