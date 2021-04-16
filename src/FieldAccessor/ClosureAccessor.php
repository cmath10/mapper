<?php

namespace cmath10\Mapper\FieldAccessor;

use Closure;

final class ClosureAccessor implements AccessorInterface
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function getValue($source)
    {
        $closure = $this->closure;

        return $closure($source);
    }
}
