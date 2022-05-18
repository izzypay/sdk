<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Traits;

use IzzyPay\Traits\HmacTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \IzzyPay\Traits\HmacTrait
 */
class HmacTraitTest extends TestCase
{
    use HmacTrait;

    protected function setUp(): void
    {
        $this->hmacAlgorithm = 'sha384';
        $this->merchantSecret = 'secret';
    }

    /**
     * @dataProvider getSignatureProvider
     */
    public function testGetSignature($authorizationHeader, $expected): void
    {
        $signature = $this->getSignature($authorizationHeader);
        $this->assertEquals($expected, $signature);
    }

    public function testGenerateSignature()
    {
        $data = json_encode(['key' => 'value'], JSON_THROW_ON_ERROR);
        $signature = $this->generateSignature($data);
        $this->assertEquals('zDWTmMuXCqhVfqSPxhGG3PBdulkQWM0ihAjd4HkZTzQW+3iCyKX7hM4Bdgimr3+f', $signature);
    }

    public function testGenerateAuthorizationHeader(): void
    {
        $merchant = 'merchant';
        $data = json_encode(['key' => 'value'], JSON_THROW_ON_ERROR);
        $authorizationHeader = $this->generateAuthorizationHeader($merchant, $data);
        $signature = $this->generateSignature($data);
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
}
