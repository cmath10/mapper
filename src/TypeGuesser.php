<?php

namespace cmath10\Mapper;

use function get_class;
use function gettype;

final class TypeGuesser
{
    /** @var TypeFilterInterface[] */
    private array $typeFilters;

    /**
     * @param TypeFilterInterface[] $typeFilters
     */
    public function __construct(array $typeFilters = [])
    {
        $this->typeFilters = $typeFilters;
    }

    public function guess($guessable): string
    {
        switch (true) {
            case is_array($guessable):
                return 'array';
            case is_object($guessable):
                return $this->filter(get_class($guessable));
        }

        return gettype($guessable);
    }

    private function filter($className): string
    {
        $result = $className;

        foreach ($this->typeFilters as $filter) {
            $result = $filter->filter($result);
        }

        return $result;
    }
}
