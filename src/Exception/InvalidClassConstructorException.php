<?php

namespace cmath10\Mapper\Exception;

use Throwable;
use function sprintf;

final class InvalidClassConstructorException extends LogicException
{
    public function __construct($className, Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'Constructor for class "%s" is invalid. Should not have required arguments.',
                $className
            ),
            0,
            $previous
        );
    }
}
