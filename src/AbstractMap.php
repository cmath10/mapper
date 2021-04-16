<?php

namespace cmath10\Mapper;

use cmath10\Mapper\FieldAccessor\AccessorInterface;
use cmath10\Mapper\FieldAccessor\PropertyPathAccessor;
use cmath10\Mapper\FieldFilter\FilterInterface;
use ReflectionClass;
use ReflectionException;

abstract class AbstractMap implements MapInterface
{
    /** @var AccessorInterface[] */
    protected array $fieldAccessors = [];

    /** @var FilterInterface[] */
    protected array $fieldFilters = [];

    protected array $fieldRoutes = [];

    protected bool $overwriteIfSet = true;

    protected bool $skipNull = false;

    public function getFieldAccessors(): array
    {
        return $this->fieldAccessors;
    }

    public function getFieldFilters(): array
    {
        return $this->fieldFilters;
    }

    public function getFieldRoutes(): array
    {
        return $this->fieldRoutes;
    }

    public function getOverwriteIfSet(): bool
    {
        return $this->overwriteIfSet;
    }

    /**
     * Sets whether to overwrite the destination value if it is already set.
     *
     * @param $value
     * @return $this
     */
    public function setOverwriteIfSet(bool $value): self
    {
        $this->overwriteIfSet = $value;

        return $this;
    }

    public function getSkipNull(): bool
    {
        return $this->skipNull;
    }

    /**
     * Sets whether to skip the source value if it is null.
     *
     * @param $value
     * @return $this
     */
    public function setSkipNull($value): self
    {
        $this->skipNull = (bool) $value;

        return $this;
    }

    public function route(string $destinationMember, string $sourceMember): MapInterface
    {
        $this->fieldAccessors[$destinationMember] = new PropertyPathAccessor(
            $this->getPropertyPath($sourceMember)
        );
        $this->fieldRoutes[$destinationMember] = $sourceMember;

        return $this;
    }

    public function forMember(string $destinationMember, AccessorInterface $fieldMapper): MapInterface
    {
        $this->fieldAccessors[$destinationMember] = $fieldMapper;

        return $this;
    }

    public function filter(string $destinationMember, FilterInterface $fieldFilter): MapInterface
    {
        $this->fieldFilters[$destinationMember] = $fieldFilter;

        return $this;
    }

    /**
     * Ignore the destination field.
     *
     * @param string $destinationMember
     * @return AbstractMap
     */
    public function ignoreMember(string $destinationMember): MapInterface
    {
        unset($this->fieldAccessors[$destinationMember]);

        return $this;
    }

    /**
     * @return $this
     * @throws ReflectionException
     */
    public function setupDefaults(): MapInterface
    {
        $reflection = new ReflectionClass($this->getDestinationType());

        foreach ($reflection->getProperties() as $property) {
            $this->fieldAccessors[$property->name] = new PropertyPathAccessor(
                $this->getPropertyPath($property->name)
            );
        }

        return $this;
    }

    protected function getPropertyPath($name): string
    {
        return $this->getSourceType() === 'array' ? '[' . $name . ']' : $name;
    }
}
