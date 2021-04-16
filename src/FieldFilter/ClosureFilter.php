<?php

namespace cmath10\Mapper\FieldFilter;

use Closure;

final class ClosureFilter implements FilterInterface
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function filter($value)
    {
        $closure = $this->closure;

        return $closure($value);
    }
}
