<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Validators\OtherValidator;

class StartOther
{
    private string $ip;
    protected string $browser;

    /**
     * @param string $ip
     * @param string $browser
     */
    private function __construct(string $ip, string $browser)
    {
        $this->ip = $ip;
        $this->browser = $browser;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return StartOther
     */
    public function setIp(string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getBrowser(): string
    {
        return $this->browser;
    }

    /**
     * @param string $browser
     * @return StartOther
     */
    public function setBrowser(string $browser): self
    {
        $this->browser = $browser;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ip' => $this->ip,
            'browser' => $this->browser,
        ];
    }

    /**
     * @param string $ip
     * @param string $browser
     * @return static
     * @throws InvalidOtherException
     */
    public static function create(string $ip, string $browser): self
    {
        $other = new StartOther($ip, $browser);

        $otherValidator = new OtherValidator();
        $otherValidator->validateStartOther($other);

        return $other;
    }
}
