<?php

declare(strict_types=1);

namespace IzzyPay\Services;

class HmacService
{
    private const HMAC_ALGORITHM = 'sha384';

    private string $hmacAlgorithm;
    private string $merchantSecret;

    /**
     * @param string $merchantSecret
     */
    public function __construct(string $merchantSecret)
    {
        $this->hmacAlgorithm = self::HMAC_ALGORITHM;
        $this->merchantSecret = $merchantSecret;
    }

    /**
     * @param string $authorizationHeader
     * @return ?string
     */
    public function getSignature(string $authorizationHeader): ?string
    {
        if (preg_match('/HMAC\s(.+)/', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param string $body
     * @return string
     */
    public function generateSignature(string $body): string
    {
        return base64_encode(hash_hmac($this->hmacAlgorithm, $body, $this->merchantSecret, true));
    }

    /**
     * @param string $merchantId
     * @param string $body
     * @return string
     */
    public function generateAuthorizationHeader(string $merchantId, string $body = ''): string
    {
        $signature = $this->generateSignature($body);
        return "HMAC $merchantId:$signature";
    }
}
