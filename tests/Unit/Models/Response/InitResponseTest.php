<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models\Response;

use IzzyPay\Models\Response\InitResponse;
use PHPUnit\Framework\TestCase;

class InitResponseTest extends TestCase
{
    private const TOKEN = 'token';
    private const MERCHANT_ID = 'merchantId';
    private const MERCHANT_CART_ID = 'merchantCartId';
    private const JS_URL = 'jsUrl';

    public function testGetters(): void
    {
        $initResponse = new InitResponse(self::TOKEN, self::MERCHANT_ID, self::MERCHANT_CART_ID, self::JS_URL);
        $this->assertEquals(self::TOKEN, $initResponse->getToken());
        $this->assertEquals(self::MERCHANT_ID, $initResponse->getMerchantId());
        $this->assertEquals(self::MERCHANT_CART_ID, $initResponse->getMerchantCartId());
        $this->assertEquals(self::JS_URL, $initResponse->getJsUrl());
    }
}
