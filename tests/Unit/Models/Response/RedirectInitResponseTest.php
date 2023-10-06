<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models\Response;

use IzzyPay\Models\Response\RedirectInitResponse;
use PHPUnit\Framework\TestCase;

class RedirectInitResponseTest extends TestCase
{
    private const TOKEN = 'token';
    private const MERCHANT_ID = 'merchantId';
    private const MERCHANT_CART_ID = 'merchantCartId';

    public function testGetters(): void
    {
        $initResponse = new RedirectInitResponse(self::TOKEN, self::MERCHANT_ID, self::MERCHANT_CART_ID);
        $this->assertEquals(self::TOKEN, $initResponse->getToken());
        $this->assertEquals(self::MERCHANT_ID, $initResponse->getMerchantId());
        $this->assertEquals(self::MERCHANT_CART_ID, $initResponse->getMerchantCartId());
    }
}
