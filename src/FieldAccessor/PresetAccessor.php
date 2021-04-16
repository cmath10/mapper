<?php

namespace cmath10\Mapper\FieldAccessor;

final class PresetAccessor implements AccessorInterface
{
    /** @var mixed */
    private $preset;

    /**
     * @param $preset mixed
     */
    public function __construct($preset)
    {
        $this->preset = $preset;
    }

    public function getValue($source)
    {
        return $this->preset;
    }
}
