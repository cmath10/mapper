<?php

namespace cmath10\Mapper;

interface TypeFilterInterface
{
    public function filter($typeName): string;
}
