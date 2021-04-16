<?php

namespace cmath10\Mapper\Exception;

use Throwable;

final class UnsupportedDestinationTypeException extends LogicException
{
    public function __construct($typeName, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('There is no map that support this destination type: %s', $typeName),
            0,
            $previous
        );
    }
}
