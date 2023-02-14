<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Validators\UrlsValidator;

class Urls
{
    private string $ipn;
    private string $checkoutUrl;

    /**
     * @param string $ipn
     * @param string $checkoutUrl
     */
    private function __construct(string $ipn, string $checkoutUrl)
    {
        $this->ipn = $ipn;
        $this->checkoutUrl = $checkoutUrl;
    }

    /**
     * @return string
     */
    public function getIpn(): string
    {
        return $this->ipn;
    }

    /**
     * @param string $ipn
     * @return Urls
     */
    public function setIpn(string $ipn): self
    {
        $this->ipn = $ipn;
        return $this;
    }

    /**
     * @return string
     */
    public function getCheckoutUrl(): string
    {
        return $this->checkoutUrl;
    }

    /**
     * @param string $checkoutUrl
     * @return Urls
     */
    public function setCheckoutUrl(string $checkoutUrl): self
    {
        $this->checkoutUrl = $checkoutUrl;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ipn' => $this->ipn,
            'checkoutUrl' => $this->checkoutUrl,
        ];
    }

    /**
     * @param string $ipn
     * @param string|null $checkoutUrl
     * @return static
     * @throws InvalidUrlsException
     */
    public static function create(string $ipn, ?string $checkoutUrl): self
    {
        $urls = new Urls($ipn, $checkoutUrl);

        $urlsValidator = new UrlsValidator();
        $urlsValidator->validateUrls($urls);

        return $urls;
    }
}
