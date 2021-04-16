<?php

namespace cmath10\Mapper;

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
}
