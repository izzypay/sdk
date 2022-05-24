<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models\Response;

use IzzyPay\Models\Response\StartResponse;
use PHPUnit\Framework\TestCase;

class StartResponseTest extends TestCase
{
    private const TOKEN = 'token';
    private const MERCHANT_ID = 'merchantId';
    private const MERCHANT_CART_ID = 'merchantCartId';

    public function testGetters(): void
    {
        $startResponse = new StartResponse(self::TOKEN, self::MERCHANT_ID, self::MERCHANT_CART_ID);
        $this->assertEquals(self::TOKEN, $startResponse->getToken());
        $this->assertEquals(self::MERCHANT_ID, $startResponse->getMerchantId());
        $this->assertEquals(self::MERCHANT_CART_ID, $startResponse->getMerchantCartId());
    }
}
