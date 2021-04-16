<?php

namespace cmath10\Mapper\FieldAccessor;

interface AccessorInterface
{
    /**
     * Gets the value for the member given the source object.
     *
     * @param mixed $source The source object
     * @return mixed The value
     */
    public function getValue($source);
}
