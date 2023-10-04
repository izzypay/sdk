<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Validators\RedirectUrlsValidator;

class RedirectUrls
{
    private string $accepted;
    private string $rejected;
    private string $cancelled;
    private string $ipn;
    private string $checkoutUrl;

    /**
     * @param string $accepted
     * @param string $rejected
     * @param string $cancelled
     * @param string $ipn
     * @param string $checkoutUrl
     */
    private function __construct(string $accepted, string $rejected, string $cancelled, string $ipn, string $checkoutUrl)
    {
        $this->accepted = $accepted;
        $this->rejected = $rejected;
        $this->cancelled = $cancelled;
        $this->ipn = $ipn;
        $this->checkoutUrl = $checkoutUrl;
    }

    /**
     * @return string
     */
    public function getAccepted(): string
    {
        return $this->accepted;
    }

    /**
     * @param string $accepted
     * @return RedirectUrls
     */
    public function setAccepted(string $accepted): self
    {
        $this->accepted = $accepted;
        return $this;
    }

    /**
     * @return string
     */
    public function getRejected(): string
    {
        return $this->rejected;
    }

    /**
     * @param string $rejected
     * @return RedirectUrls
     */
    public function setRejected(string $rejected): self
    {
        $this->rejected = $rejected;
        return $this;
    }

    /**
     * @return string
     */
    public function getCancelled(): string
    {
        return $this->cancelled;
    }

    /**
     * @param string $cancelled
     * @return RedirectUrls
     */
    public function setCancelled(string $cancelled): self
    {
        $this->cancelled = $cancelled;
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
            'acceptedUrl' => $this->accepted,
            'rejectedUrl' => $this->rejected,
            'cancelledUrl' => $this->cancelled,
            'ipn' => $this->ipn,
            'checkoutUrl' => $this->checkoutUrl,
        ];
    }

    /**
     * @param string $accepted
     * @param string $rejected
     * @param string $cancelled
     * @param string $ipn
     * @param string $checkoutUrl
     * @return RedirectUrls
     * @throws InvalidUrlsException
     */
    public static function create(string $accepted, string $rejected, string $cancelled, string $ipn, string $checkoutUrl): self
    {
        $urls = new RedirectUrls($accepted, $rejected, $cancelled, $ipn, $checkoutUrl);

        $urlsValidator = new RedirectUrlsValidator();
        $urlsValidator->validateUrls($urls);

        return $urls;
    }
}
