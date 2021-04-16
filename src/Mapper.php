<?php

namespace cmath10\Mapper;

use cmath10\Mapper\Exception\InvalidClassConstructorException;
use cmath10\Mapper\FieldAccessor\PropertyPathAccessor;
use cmath10\Mapper\FieldFilter\AbstractMappingFilter;
use ArrayAccess;
use LogicException;
use ReflectionClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use function is_array;
use function is_null;
use function is_string;
use function sprintf;

final class Mapper implements MapperInterface
{
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

    public function create(string $sourceType, string $destinationMap): MapInterface
    {
        return $this->maps[$sourceType][$destinationMap] = new DefaultMap($sourceType, $destinationMap);
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

            if ($map->getSkipNull() && is_null($value)) {
                continue;
            }

            if ($map->getOverwriteIfSet() || $this->accessor->getValue($destination, $path) === null) {
                $this->accessor->setValue($destination, $path, $value);
            }
        }

        return $destination;
    }

    private function get(string $sourceType, string $destinationType): AbstractMap
    {
        if (!isset($this->maps[$sourceType])) {
            throw new LogicException(sprintf(
                'There is no map that support this source type: %s',
                $sourceType
            ));
        }

        if (!isset($this->maps[$sourceType][$destinationType])) {
            throw new LogicException(sprintf(
                'There is no map that support this destination type: %s',
                $destinationType
            ));
        }

        return $this->maps[$sourceType][$destinationType];
    }
}

