<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Validators\OtherValidator;

class Other
{
    private string $ip;
    private string $browser;
    private string $os;

    /**
     * @param string $ip
     * @param string $browser
     * @param string $os
     */
    private function __construct(string $ip, string $browser, string $os)
    {
        $this->ip = $ip;
        $this->browser = $browser;
        $this->os = $os;
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
     * @return Other
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
     * @return Other
     */
    public function setBrowser(string $browser): self
    {
        $this->browser = $browser;
        return $this;
    }

    /**
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * @param string $os
     * @return Other
     */
    public function setOs(string $os): self
    {
        $this->os = $os;
        return $this;
    }

    /**
     * @param string $ip
     * @param string $browser
     * @param string $os
     * @return static
     * @throws InvalidOtherException
     */
    public static function create(string $ip, string $browser, string $os): self
    {
        $other = new Other($ip, $browser, $os);

        $otherValidator = new OtherValidator();
        $invalidFields = $otherValidator->validateOther($other);
        if (count($invalidFields) > 0) {
            throw new InvalidOtherException($invalidFields);
        }

        return $other;
    }
}
