<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Models\Other;
use IzzyPay\Models\StartOther;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class StartOtherTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const BROWSER = 'Chrome';
    private const IP = '192.168.1.1';

    protected function setUp(): void
    {
        $this->fields = [
            'browser' => 'Vivaldi',
            'ip' => '192.168.1.2',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $other = $this->invokeConstructor(StartOther::class, [self::IP, self::BROWSER]);
        $this->_testSettersAndGetters($other);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        /** @var StartOther $other */
        $other = $this->invokeConstructor(StartOther::class, [self::IP, self::BROWSER]);
        $otherAsArray = $other->toArray();
        $this->assertEqualsCanonicalizing(
            [
                'ip' => self::IP,
                'browser' => self::BROWSER
            ],
            $otherAsArray
        );
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidOtherException::class);
        StartOther::create('', '');
    }

    /**
     * @throws InvalidOtherException
     */
    public function testCreate(): void
    {
        $other = StartOther::create(self::IP, self::BROWSER);
        $this->assertEquals(self::BROWSER, $other->getBrowser());
        $this->assertEquals(self::IP, $other->getIp());
    }
}
