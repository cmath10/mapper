<?php

namespace cmath10\Mapper\FieldFilter;

interface FilterInterface
{
    /**
     * Applies the filter to a given value.
     *
     * @param $value mixed The value to filter
     * @return mixed The filtered value
     */
    public function filter($value);
}
