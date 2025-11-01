<?php

namespace App\Pipeline;

use Closure;
use Illuminate\Database\Eloquent\Builder;

interface QueryPipeline
{
    public function handle(Builder $query, Closure $next): mixed;
}
