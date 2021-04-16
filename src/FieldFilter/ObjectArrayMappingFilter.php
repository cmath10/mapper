<?php

namespace cmath10\Mapper\FieldFilter;

use function array_map;
use function is_array;

final class ObjectArrayMappingFilter extends AbstractMappingFilter
{
    public function filter($value): array
    {
        if (is_array($value)) {
            $objectFilter = new ObjectMappingFilter($this->className);
            $objectFilter->setMapper($this->getMapper());

            $mapFn = static fn($item) => $objectFilter->filter($item);

            return array_map($mapFn, $value);
        }

        return [];
    }
}
