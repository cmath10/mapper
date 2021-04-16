<?php

namespace cmath10\Mapper;

use cmath10\Mapper\FieldAccessor\PropertyPathAccessor;
use ReflectionClass;

final class DefaultMap extends AbstractMap
{
    private string $sourceType;
    private string $destinationType;

    public function __construct(string $sourceType, string $destinationMap)
    {
        $this->sourceType = $sourceType;
        $this->destinationType = $destinationMap;

        $this->setupDefaults();
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function getDestinationType(): string
    {
        return $this->destinationType;
    }

    public function route(string $destinationMember, string $sourceMember): MapInterface
    {
        $this->fieldAccessors[$destinationMember] = new PropertyPathAccessor(
            $this->getPropertyPath($sourceMember)
        );

        return $this;
    }
}
