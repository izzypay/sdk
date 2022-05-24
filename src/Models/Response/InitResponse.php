<?php

declare(strict_types=1);

namespace IzzyPay\Models\Response;

class InitResponse extends AbstractResponse
{
    private string $jsUrl;

    /**
     * @param string $token
     * @param string $merchantId
     * @param string $merchantCartId
     * @param string $jsUrl
     */
    public function __construct(string $token, string $merchantId, string $merchantCartId, string $jsUrl)
    {
        parent::__construct($token, $merchantId, $merchantCartId);
        $this->jsUrl = $jsUrl;
    }

    /**
     * @return string
     */
    public function getJsUrl(): string
    {
        return $this->jsUrl;
    }
}
