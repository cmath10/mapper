<?php

namespace cmath10\Mapper;

final class DefaultMap extends AbstractMap
{
    private string $sourceType;
    private string $destinationType;

    public function __construct(string $sourceType, string $destinationType)
    {
        $this->sourceType = $sourceType;
        $this->destinationType = $destinationType;

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
