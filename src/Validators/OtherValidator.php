<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Models\Other;

class OtherValidator
{
    /**
     * @param Other $other
     * @return void
     * @throws InvalidOtherException
     */
    public function validateOther(Other $other): void
    {
        $errors = [];

        if (!filter_var($other->getIp(), FILTER_VALIDATE_IP)) {
            $errors[] = 'ip';
        }

        if (count($errors) > 0) {
            throw new InvalidOtherException($errors);
        }
    }
}
