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

    private const IP = '192.168.1.1';
    private const BROWSER = 'Chrome';
    private const OS = 'Linux';

    protected function setUp(): void
    {
        $this->fields = [
            'ip' => '192.168.1.2',
            'browser' => 'Vivaldi',
            'os' => 'Windows',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $other = $this->invokeConstructor(Other::class, [self::IP, self::BROWSER, self::OS]);
        $this->_testSettersAndGetters($other);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $other = $this->invokeConstructor(Other::class, [self::IP, self::BROWSER, self::OS]);
        $otherAsArray = $other->toArray();
        $this->assertEqualsCanonicalizing([
            'ip' => self::IP,
            'browser' => self::BROWSER,
            'os' => self::OS,
        ], $otherAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidOtherException::class);
        Other::create('invalid', 'browser', 'os');
    }

    /**
     * @throws InvalidOtherException
     */
    public function testCreate(): void
    {
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $this->assertEquals(self::IP, $other->getIp());
        $this->assertEquals(self::BROWSER, $other->getBrowser());
        $this->assertEquals(self::OS, $other->getOs());
    }
}
