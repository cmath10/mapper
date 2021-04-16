<?php

namespace cmath10\Mapper\FieldFilter;

final class ObjectMappingFilter extends AbstractMappingFilter
{
    public function filter($value): ?object
    {
        if ($value) {
            return $this->getMapper()->map($value, $this->className);
        }

        return null;
    }
}
