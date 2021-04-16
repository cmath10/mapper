<?php

namespace cmath10\Mapper;

use cmath10\Mapper\FieldAccessor\AccessorInterface;
use cmath10\Mapper\FieldFilter\FilterInterface;

interface MapInterface
{
    /**
     * @return string The source type
     */
    public function getSourceType(): string;

    /**
     * @return string The destination type
     */
    public function getDestinationType(): string;

    /**
     * @return AccessorInterface[] An array of field accessors
     */
    public function getFieldAccessors(): array;

    /**
     * @return FilterInterface[] An array of field filters
     */
    public function getFieldFilters(): array;

    /**
     * Associate a member to another member given their property paths.
     *
     * @param string $destinationMember
     * @param string $sourceMember
     *
     * @return $this Current instance of map
     */
    public function route(string $destinationMember, string $sourceMember): self;

    /**
     * Applies a field accessor policy to a member.
     *
     * @param string $destinationMember
     * @param AccessorInterface $fieldMapper
     * @return $this Current instance of map
     */
    public function forMember(string $destinationMember, AccessorInterface $fieldMapper): self;

    /**
     * Applies a filter to the field.
     *
     * @param string $destinationMember
     * @param FilterInterface $fieldFilter
     * @return $this Current instance of map
     */
    public function filter(string $destinationMember, FilterInterface $fieldFilter): self;
}
