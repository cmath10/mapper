<?php

namespace cmath10\Mapper\FieldAccessor;

use cmath10\Mapper\Exception\InvalidSourceProperty;
use Exception;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use function is_array;
use function preg_match;

final class ExpressionAccessor implements AccessorInterface
{
    /** @var string|Expression */
    private $expression;

    /**
     * @param string|Expression $sourcePropertyExpression
     */
    public function __construct($sourcePropertyExpression)
    {
        $this->expression = $sourcePropertyExpression;
    }

    public function getValue($source)
    {
        $interpreter = new ExpressionLanguage();

        try {
            $separator = is_array($source) ? '' : '.';
            $expression = 'value' . $separator . $this->expression;

            return $interpreter->evaluate($expression, ['value' => $source]);
        } catch (Exception $e) {
            if (!$this->matches('/Unable to get property ".*" of non-object/', $e)) {
                if ($this->matches('/Variable ".*" is not valid/', $e)) {
                    throw new InvalidSourceProperty('Property path "'.$this->expression.'" is invalid');
                }

                throw $e;
            }

            return null;
        }
    }

    private function matches(string $pattern, Exception $exception): bool
    {
        return (bool)preg_match($pattern, $exception->getMessage());
    }
}
