<?php

namespace cmath10\Mapper\Tests\Fixtures;

final class Magazine
{
    public $articles;

    public function __construct(array $articles = [])
    {
        $this->articles = $articles;
    }
}
