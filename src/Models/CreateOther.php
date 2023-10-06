<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Validators\OtherValidator;

class CreateOther extends StartOther
{
    /**
     * @param string $ip
     * @param string $browser
     * @return CreateOther
     * @throws InvalidOtherException
     */
    public static function create(string $ip, string $browser): self
    {
        $other = new CreateOther($ip, $browser);

        $otherValidator = new OtherValidator();
        $otherValidator->validateCreateOther($other);

        return $other;
    }
}
