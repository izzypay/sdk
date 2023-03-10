<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Validators\OtherValidator;

class Other
{
    private string $browser;

    /**
     * @param string $browser
     */
    private function __construct(string $browser)
    {
        $this->browser = $browser;
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'browser' => $this->browser,
        ];
    }

    /**
     * @param string $browser
     * @return static
     * @throws InvalidOtherException
     */
    public static function create(string $browser): self
    {
        $other = new Other($browser);

        $otherValidator = new OtherValidator();
        $otherValidator->validateOther($other);

        return $other;
    }
}
