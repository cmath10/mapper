<?php

namespace  cmath10\Mapper\FieldFilter;

final class IfNullFilter implements FilterInterface
{
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value The value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns a default value if the original is null
     *
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        return $value ?? $this->value;
    }
}
