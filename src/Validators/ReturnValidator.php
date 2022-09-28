<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use DateTime;
use IzzyPay\Exceptions\InvalidReturnDataException;

class ReturnValidator
{
    /**
     * @param string $returnDate
     * @param float|null $reducedValue
     * @return void
     * @throws InvalidReturnDataException
     */
    public function validate(string $returnDate, ?float $reducedValue = null): void
    {
        $errors = [];

        $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:sP', $returnDate);
        if (!$dateTime) {
            $errors[] = 'returnDate';
        }

        if ($reducedValue && ($reducedValue < 0)) {
            $errors[] = 'reducedValue';
        }

        if (count($errors) > 0) {
            throw new InvalidReturnDataException($errors);
        }
    }
}
