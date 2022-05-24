<?php

declare(strict_types=1);

namespace IzzyPay\Models\Response;

class AbstractResponse
{
    protected string $token;
    protected string $merchantId;
    protected string $merchantCartId;

    /**
     * @param string $token
     * @param string $merchantId
     * @param string $merchantCartId
     */
    public function __construct(string $token, string $merchantId, string $merchantCartId)
    {
        $this->token = $token;
        $this->merchantId = $merchantId;
        $this->merchantCartId = $merchantCartId;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getMerchantCartId(): string
    {
        return $this->merchantCartId;
    }
}
