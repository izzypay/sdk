<?php

declare(strict_types=1);

namespace IzzyPay\Models\Response;

class CreateResponse extends AbstractResponse
{
    private string $redirectUrl;

    /**
     * @param string $token
     * @param string $merchantId
     * @param string $merchantCartId
     * @param string $redirectUrl
     */
    public function __construct(string $token, string $merchantId, string $merchantCartId, string $redirectUrl)
    {
        parent::__construct($token, $merchantId, $merchantCartId);
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
