<?php

namespace cmath10\Mapper\Tests\Fixtures;

final class Article
{
    public $title;

    public $text;

    public $author;

    public function __construct(?string $title = null)
    {
        $this->title = $title;
    }
}
