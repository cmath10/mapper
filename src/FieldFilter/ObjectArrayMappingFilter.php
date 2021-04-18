<?php

namespace cmath10\Mapper\FieldFilter;

use function array_map;
use function is_array;

final class ObjectArrayMappingFilter extends AbstractMappingFilter
{
    public function filter($value): array
    {
        if (is_array($value)) {
            $filter = new ObjectMappingFilter($this->className);
            $filter->setMapper($this->getMapper());

            $mapFn = static fn ($item) => $filter->filter($item);

            return array_map($mapFn, $value);
        }

        return [];
    }
}
