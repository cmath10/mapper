<?php

namespace cmath10\Mapper;

use cmath10\Mapper\Exception\InvalidClassConstructorException;

interface MapperInterface
{
    /**
     * Creates default map
     *
     * @param string $sourceType
     * @param string $destinationType
     *
     * @return MapInterface
     */
    public function create(string $sourceType, string $destinationType): MapInterface;

    /**
     * Adds map to mapper
     *
     * @param MapInterface $map
     */
    public function register(MapInterface $map): void;

    /**
     * Maps two object together, a map should exist.
     *
     * @param mixed $source
     * @param mixed $destination
     *
     * @return object
     *
     * @throws InvalidClassConstructorException
     */
    public function map($source, $destination): object;
}
