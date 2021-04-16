<?php

namespace cmath10\Mapper\Tests\FieldAccessor;

use cmath10\Mapper\Exception\InvalidSourcePropertyException;
use cmath10\Mapper\FieldAccessor\ExpressionAccessor;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ExpressionAccessorTest extends TestCase
{
    public function testAccessObject(): void
    {
        $accessor = new ExpressionAccessor('field');

        $origin = new stdClass();
        $origin->field = 'value';

        self::assertEquals('value', $accessor->getValue($origin));
    }

    public function testAccessArray(): void
    {
        $accessor = new ExpressionAccessor('["friends"][0]["details"].name');

        $details = new stdClass();
        $details->name = 'Josh';

        self::assertEquals('Josh', $accessor->getValue([
            'friends' => [['details' => $details]],
        ]));
    }

    public function testAccessFail(): void
    {
        $this->expectException(InvalidSourcePropertyException::class);

        $accessor = new ExpressionAccessor('friends.details.name');

        self::assertEquals(null, $accessor->getValue([
            'friends' => [['details' => ['name' => 'Josh']]],
        ]));
    }

    public function testAccessString(): void
    {
        $accessor = new ExpressionAccessor('credentials.username');

        self::assertEquals(null, $accessor->getValue('user'));
    }
}
