<?php

namespace cmath10\Mapper\Tests\Fixtures;

final class Author
{
    public $id;

    public $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }
}
