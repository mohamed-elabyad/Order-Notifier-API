<?php

namespace App\Pipeline;

use Illuminate\Database\Eloquent\Builder;
use Closure;

class DateRangeFilter implements QueryPipeline
{
    public function __construct(
        private ?string $from,
        private ?string $to
    ) {}

    public function handle(Builder $query, Closure $next): mixed
    {
        $query->when($this->from,
        fn($q, $from) => $q->whereDate('placed_at', '>=', $from));

        $query->when($this->to,
        fn($q, $to) => $q->whereDate('placed_at', '<=', $to));

        return $next($query);
    }
}
