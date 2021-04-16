<?php

namespace cmath10\Mapper\Tests\Fixtures;

use cmath10\Mapper\AbstractMap;

final class ArticleOutputMap extends AbstractMap
{
    public function __construct()
    {
        $this
            ->setupDefaults()
            ->route('textNotMappedByDefault', 'text')
        ;
    }

    public function getSourceType(): string
    {
        return Article::class;
    }

    public function getDestinationType(): string
    {
        return ArticleOutput::class;
    }
}
