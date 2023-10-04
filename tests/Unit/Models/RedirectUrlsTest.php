<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Models\RedirectUrls;
use IzzyPay\Models\Urls;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class RedirectUrlsTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const ACCEPTED_URL = 'https://accepted.com';
    private const REJECTED_URL = 'https://rejected.com';
    private const CANCELLED_URL = 'https://cancelled.com';
    private const IPN_URL = 'https://ipn.com';
    private const CHECKOUT_URL = 'https://checkout.com';

    protected function setUp(): void
    {
        $this->fields = [
            'accepted' => 'https://www.accepted.com',
            'rejected' => 'https://www.rejected.com',
            'cancelled' => 'https://www.cancelled.com',
            'ipn' => 'https://www.ipn.com',
            'checkoutUrl' => 'https://www.checkout.com'
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $urls = $this->invokeConstructor(
            RedirectUrls::class,
            [self::ACCEPTED_URL, self::REJECTED_URL, self::CANCELLED_URL, self::IPN_URL, self::CHECKOUT_URL]
        );
        $this->_testSettersAndGetters($urls);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        /** @var RedirectUrls $urls */
        $urls = $this->invokeConstructor(
            RedirectUrls::class,
            [self::ACCEPTED_URL, self::REJECTED_URL, self::CANCELLED_URL, self::IPN_URL, self::CHECKOUT_URL]
        );
        $urlsAsArray = $urls->toArray();
        $this->assertEquals(
            [
                'acceptedUrl' => self::ACCEPTED_URL,
                'rejectedUrl' => self::REJECTED_URL,
                'cancelledUrl' => self::CANCELLED_URL,
                'ipn' => self::IPN_URL,
                'checkoutUrl' => self::CHECKOUT_URL
            ],
            $urlsAsArray
        );
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
        $urls = RedirectUrls::create(
            self::ACCEPTED_URL,
            self::REJECTED_URL,
            self::CANCELLED_URL,
            self::IPN_URL,
            self::CHECKOUT_URL
        );
        $this->assertEquals(self::ACCEPTED_URL, $urls->getAccepted());
        $this->assertEquals(self::REJECTED_URL, $urls->getRejected());
        $this->assertEquals(self::CANCELLED_URL, $urls->getCancelled());
        $this->assertEquals(self::IPN_URL, $urls->getIpn());
        $this->assertEquals(self::CHECKOUT_URL, $urls->getCheckoutUrl());
    }
}
