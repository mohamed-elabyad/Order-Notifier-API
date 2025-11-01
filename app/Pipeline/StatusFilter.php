<?php

namespace App\Pipeline;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter implements QueryPipeline
{
    public function __construct(private ?string $value) {}

    public function handle(Builder $query, Closure $next): mixed
    {
        if (!empty($this->value)) {
            $statuses = array_map('trim', explode(',', $this->value));
            $query->whereIn('status', $statuses);
        }

        return $next($query);
    }
}
