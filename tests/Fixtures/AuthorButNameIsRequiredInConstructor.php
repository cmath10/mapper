<?php

namespace cmath10\Mapper\Tests\Fixtures;

final class AuthorButNameIsRequiredInConstructor
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
