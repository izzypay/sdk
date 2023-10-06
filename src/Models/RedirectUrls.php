<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Validators\RedirectUrlsValidator;

class RedirectUrls
{
    private string $acceptedUrl;
    private string $rejectedUrl;
    private string $cancelledUrl;
    private string $ipn;
    private string $checkoutUrl;

    /**
     * @param string $acceptedUrl
     * @param string $rejectedUrl
     * @param string $cancelledUrl
     * @param string $ipn
     * @param string $checkoutUrl
     */
    private function __construct(string $acceptedUrl, string $rejectedUrl, string $cancelledUrl, string $ipn, string $checkoutUrl)
    {
        $this->acceptedUrl = $acceptedUrl;
        $this->rejectedUrl = $rejectedUrl;
        $this->cancelledUrl = $cancelledUrl;
        $this->ipn = $ipn;
        $this->checkoutUrl = $checkoutUrl;
    }

    /**
     * @return string
     */
    public function getAcceptedUrl(): string
    {
        return $this->acceptedUrl;
    }

    /**
     * @param string $acceptedUrl
     * @return RedirectUrls
     */
    public function setAcceptedUrl(string $acceptedUrl): self
    {
        $this->acceptedUrl = $acceptedUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getRejectedUrl(): string
    {
        return $this->rejectedUrl;
    }

    /**
     * @param string $rejectedUrl
     * @return RedirectUrls
     */
    public function setRejectedUrl(string $rejectedUrl): self
    {
        $this->rejectedUrl = $rejectedUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getCancelledUrl(): string
    {
        return $this->cancelledUrl;
    }

    /**
     * @param string $cancelledUrl
     * @return RedirectUrls
     */
    public function setCancelledUrl(string $cancelledUrl): self
    {
        $this->cancelledUrl = $cancelledUrl;
        return $this;
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
     * @return RedirectUrls
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
     * @return RedirectUrls
     */
    public function setCheckoutUrl(string $checkoutUrl): self
    {
        $this->checkoutUrl = $checkoutUrl;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'acceptedUrl' => $this->acceptedUrl,
            'rejectedUrl' => $this->rejectedUrl,
            'cancelledUrl' => $this->cancelledUrl,
            'ipn' => $this->ipn,
            'checkoutUrl' => $this->checkoutUrl,
        ];
    }

    /**
     * @param string $acceptedUrl
     * @param string $rejectedUrl
     * @param string $cancelledUrl
     * @param string $ipn
     * @param string $checkoutUrl
     * @return RedirectUrls
     * @throws InvalidUrlsException
     */
    public static function create(string $acceptedUrl, string $rejectedUrl, string $cancelledUrl, string $ipn, string $checkoutUrl): self
    {
        $urls = new RedirectUrls($acceptedUrl, $rejectedUrl, $cancelledUrl, $ipn, $checkoutUrl);

        $urlsValidator = new RedirectUrlsValidator();
        $urlsValidator->validateUrls($urls);

        return $urls;
    }
}
