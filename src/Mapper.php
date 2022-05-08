<?php

namespace cmath10\Mapper;

use cmath10\Mapper\Exception\InvalidClassConstructorException;
use cmath10\Mapper\Exception\UnsupportedDestinationTypeException;
use cmath10\Mapper\Exception\UnsupportedSourceTypeException;
use cmath10\Mapper\FieldAccessor\PropertyPathAccessor;
use cmath10\Mapper\FieldFilter\AbstractMappingFilter;
use ArrayAccess;
use ReflectionClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use function is_array;
use function is_null;
use function is_string;

final class Mapper implements MapperInterface
{
    /**
     * @var MapInterface[][]
     */
    private array $maps = [];

    private PropertyAccessor $accessor;

    private TypeGuesser $guesser;

    /**
     * @param TypeFilterInterface[] $typeFilters
     */
    public function __construct(array $typeFilters = [])
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->guesser = new TypeGuesser($typeFilters);
    }

    public function create(string $sourceType, string $destinationType): MapInterface
    {
        return $this->maps[$sourceType][$destinationType] = new DefaultMap($sourceType, $destinationType);
    }

    public function register(MapInterface $map): void
    {
        $this->maps[$map->getSourceType()][$map->getDestinationType()] = $map;
    }

    public function map($source, $destination): object
    {
        if (is_string($destination)) {
            $destinationRef = new ReflectionClass($destination);

            if (
                $destinationRef->getConstructor() &&
                $destinationRef->getConstructor()->getNumberOfRequiredParameters() > 0
            ) {
                throw new InvalidClassConstructorException($destination);
            }

            $destination = $destinationRef->newInstance();
        }

        $map = $this->get(
            $this->guesser->guess($source),
            $this->guesser->guess($destination)
        );

        $fieldAccessors = $map->getFieldAccessors();
        $fieldFilters = $map->getFieldFilters();

        foreach ($fieldAccessors as $path => $fieldAccessor) {
            if ($fieldAccessor instanceof PropertyPathAccessor) {
                $sourcePath = $fieldAccessor->getSourcePath();
            } elseif (!empty($map->getFieldRoutes()[$path])) {
                $sourcePath = $map->getFieldRoutes()[$path];
            } else {
                $sourcePath = $path;
            }

            if (
                (is_array($source) || $source instanceof ArrayAccess) &&
                !array_key_exists($sourcePath, $source)
            ) {
                continue;
            }

            $value = $fieldAccessor->getValue($source);

            if (isset($fieldFilters[$path])) {
                $filter = $fieldFilters[$path];

                if ($filter instanceof AbstractMappingFilter) {
                    $filter->setMapper($this);
                }

                $value = $filter->filter($value);
            }

            if (is_null($value) && $map->getSkipNull()) {
                continue;
            }

            if ($map->getOverwriteIfSet() || $this->accessor->getValue($destination, $path) === null) {
                $this->accessor->setValue($destination, $path, $value);
            }
        }

        return $destination;
    }

    private function get(string $sourceType, string $destinationType): MapInterface
    {
        if (!isset($this->maps[$sourceType])) {
            throw new UnsupportedSourceTypeException($sourceType);
        }

        if (!isset($this->maps[$sourceType][$destinationType])) {
            throw new UnsupportedDestinationTypeException($destinationType);
        }

        return $this->maps[$sourceType][$destinationType];
    }
}
