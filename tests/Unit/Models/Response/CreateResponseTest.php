<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models\Response;

use IzzyPay\Models\Response\CreateResponse;
use PHPUnit\Framework\TestCase;

class CreateResponseTest extends TestCase
{
    private const TOKEN = 'token';
    private const MERCHANT_ID = 'merchantId';
    private const MERCHANT_CART_ID = 'merchantCartId';
    private const REDIRECT_URL = 'https://test.izzpay.hu/redirect';

    public function testGetters(): void
    {
        $startResponse = new CreateResponse(self::TOKEN, self::MERCHANT_ID, self::MERCHANT_CART_ID, self::REDIRECT_URL);
        $this->assertEquals(self::TOKEN, $startResponse->getToken());
        $this->assertEquals(self::MERCHANT_ID, $startResponse->getMerchantId());
        $this->assertEquals(self::MERCHANT_CART_ID, $startResponse->getMerchantCartId());
        $this->assertEquals(self::REDIRECT_URL, $startResponse->getRedirectUrl());
    }
}
