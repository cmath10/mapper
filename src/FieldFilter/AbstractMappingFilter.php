<?php

namespace cmath10\Mapper\FieldFilter;

use cmath10\Mapper\MapperInterface;

abstract class AbstractMappingFilter implements FilterInterface
{
    private MapperInterface $mapper;

    protected string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function setMapper(MapperInterface $mapper): void
    {
        $this->mapper = $mapper;
    }

    protected function getMapper(): MapperInterface
    {
        return $this->mapper;
    }
}
