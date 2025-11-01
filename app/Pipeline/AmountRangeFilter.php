<?php

namespace App\Pipeline;

use Illuminate\Database\Eloquent\Builder;
use Closure;

class AmountRangeFilter implements QueryPipeline
{

    public function __construct(
        private ?float $min,
        private ?float $max
    ) {}

    public function handle(Builder $query, Closure $next): mixed
    {
        $query->when($this->min,
        fn($q, $min) => $q->where('amount_decimal', '>=', $min));

        $query->when($this->max,
        fn($q, $max) => $q->where('amount_decimal', '<=', $max));

        return $next($query);
    }
}
