<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\InvalidReturnDataException;

class ReturnValidator
{
    /**
     * @param float|null $reducedValue
     * @return void
     * @throws InvalidReturnDataException
     */
    public function validate(?float $reducedValue): void
    {
        $errors = [];

        if (($reducedValue !== null) && ($reducedValue < 0)) {
            $errors[] = 'reducedValue';
        }

        if (count($errors) > 0) {
            throw new InvalidReturnDataException($errors);
        }
    }
}
