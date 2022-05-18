<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Models\Urls;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class UrlsTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const URL = 'https://example.com';

    protected function setUp(): void
    {
        $this->fields = [
            'ipn' => 'https://www.example.com',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $urls = $this->invokeConstructor(Urls::class, [self::URL]);
        $this->_testSettersAndGetters($urls);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $urls = $this->invokeConstructor(Urls::class, [self::URL]);
        $urlsAsArray = $urls->toArray();
        $this->assertEqualsCanonicalizing([
            'ipn' => self::URL,
        ], $urlsAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidUrlsException::class);
        Urls::create('invalid');
    }

    /**
     * @throws InvalidUrlsException
     */
    public function testCreate(): void
    {
        $urls = Urls::create(self::URL);
        $this->assertEquals(self::URL, $urls->getIpn());
    }
}
