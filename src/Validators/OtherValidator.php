<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Models\Other;

class OtherValidator
{
    /**
     * @param Other $other
     * @return array
     */
    public function validateOther(Other $other): array
    {
        $errors = [];

        if (($other->getIp() !== '') && !filter_var($other->getIp(), FILTER_VALIDATE_IP)) {
            $errors[] = 'ip';
        }

        return $errors;
    }
}
