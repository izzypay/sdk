<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Validators\UrlsValidator;

class Urls
{
    private string $ipn;

    /**
     * @param string $ipn
     */
    private function __construct(string $ipn)
    {
        $this->ipn = $ipn;
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ipn' => $this->ipn,
        ];
    }

    /**
     * @param string $ipn
     * @return static
     * @throws InvalidUrlsException
     */
    public static function create(string $ipn): self
    {
        $urls = new Urls($ipn);

        $urlsValidator = new UrlsValidator();
        $urlsValidator->validateUrls($urls);

        return $urls;
    }
}