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

    private const IPN_URL = 'https://webshop.url/ipn';
    private const CHECKOUT_URL = 'https://webshop.url/checkout';

    protected function setUp(): void
    {
        $this->fields = [
            'ipn' => 'https://www.webshop.url/ipn',
            'checkoutUrl' => 'https://www.webshop.url/checkout'
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $urls = $this->invokeConstructor(Urls::class, [self::IPN_URL, self::CHECKOUT_URL]);
        $this->_testSettersAndGetters($urls);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $urls = $this->invokeConstructor(Urls::class, [self::IPN_URL, self::CHECKOUT_URL]);
        $urlsAsArray = $urls->toArray();
        $this->assertEquals([
            'ipn' => self::IPN_URL,
            'checkoutUrl' => self::CHECKOUT_URL,
        ], $urlsAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidUrlsException::class);
        Urls::create('invalid', 'invalid');
    }

    /**
     * @throws InvalidUrlsException
     */
    public function testCreate(): void
    {
        $urls = Urls::create(self::IPN_URL, self::CHECKOUT_URL);
        $this->assertEquals(self::IPN_URL, $urls->getIpn());
        $this->assertEquals(self::CHECKOUT_URL, $urls->getCheckoutUrl());
    }
}
