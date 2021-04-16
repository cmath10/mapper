<?php

namespace cmath10\Mapper\FieldAccessor;

use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

final class PropertyPathAccessor implements AccessorInterface
{
    private PropertyPath $path;

    public function __construct(string $sourcePropertyPath)
    {
        $this->path = new PropertyPath($sourcePropertyPath);
    }

    public function getValue($source)
    {
        try {
            $accessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor()
            ;

            return $accessor->getValue($source, $this->path);
        } catch (NoSuchIndexException|NoSuchPropertyException $e) {
            return null;
        }
    }

    public function getSourcePath(): string
    {
        return str_replace(['[', ']'], '', $this->path->__toString());
    }
}
