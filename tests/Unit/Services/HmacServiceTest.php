<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Services;

use IzzyPay\Services\HmacService;
use JsonException;
use PHPUnit\Framework\TestCase;

class HmacServiceTest extends TestCase
{
    private const MERCHANT_SECRET = 'secret';

    /**
     * @dataProvider getSignatureProvider
     */
    public function testGetSignature(string $authorizationHeader, ?string $expected): void
    {
        $hmacService = $this->getNewHmacService();
        $signature = $hmacService->getSignature($authorizationHeader);
        $this->assertEquals($expected, $signature);
    }

    /**
     * @throws JsonException
     */
    public function testGenerateSignature(): void
    {
        $data = json_encode(['key' => 'value'], JSON_THROW_ON_ERROR);
        $hmacService = $this->getNewHmacService();
        $signature = $hmacService->generateSignature($data);
        $this->assertEquals('zDWTmMuXCqhVfqSPxhGG3PBdulkQWM0ihAjd4HkZTzQW+3iCyKX7hM4Bdgimr3+f', $signature);
    }

    /**
     * @throws JsonException
     */
    public function testGenerateAuthorizationHeader(): void
    {
        $merchant = 'merchant';
        $data = json_encode(['key' => 'value'], JSON_THROW_ON_ERROR);
        $hmacService = $this->getNewHmacService();
        $authorizationHeader = $hmacService->generateAuthorizationHeader($merchant, $data);
        $signature = $hmacService->generateSignature($data);
        $this->assertEquals("HMAC $merchant:$signature", $authorizationHeader);
    }

    public function getSignatureProvider(): array
    {
        return [
            ['', null],
            ['Bearer signature', null],
            ['HMAC signature', 'signature']
        ];
    }

    private function getNewHmacService(): HmacService
    {
        return new HmacService(self::MERCHANT_SECRET);
    }
}
