<?php

declare(strict_types=1);

namespace Bnpl\Traits;

trait HmacTrait
{
    private string $hmacAlgorithm;
    private string $merchantSecret;

    /**
     * @param string $authorizationHeader
     * @return ?string
     */
    public function getSignature(string $authorizationHeader): ?string
    {
        if (preg_match('/HMAC\s((.+):(.+))/', $authorizationHeader, $matches)) {
            return $matches[3];
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
