<?php

namespace cmath10\Mapper\Tests\Fixtures;

final class MagazineWithPrivateProperties
{
    private array $articles;

    public function __construct(array $articles = [])
    {
        $this->articles = $articles;
    }

    public function getArticles(): array
    {
        return $this->articles;
    }
}
