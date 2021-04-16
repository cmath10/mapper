<?php

namespace cmath10\Mapper\Exception;

use Throwable;

final class UnsupportedSourceTypeException extends LogicException
{
    public function __construct($typeName, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('There is no map that support this source type: %s', $typeName),
            0,
            $previous
        );
    }
}
