<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Models\Other;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class OtherTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const BROWSER = 'Chrome';

    protected function setUp(): void
    {
        $this->fields = [
            'browser' => 'Vivaldi',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $other = $this->invokeConstructor(Other::class, [self::BROWSER]);
        $this->_testSettersAndGetters($other);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $other = $this->invokeConstructor(Other::class, [self::BROWSER]);
        $otherAsArray = $other->toArray();
        $this->assertEqualsCanonicalizing([
            'browser' => self::BROWSER,
        ], $otherAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidOtherException::class);
        Other::create('');
    }

    /**
     * @throws InvalidOtherException
     */
    public function testCreate(): void
    {
        $other = Other::create(self::BROWSER);
        $this->assertEquals(self::BROWSER, $other->getBrowser());
    }
}
