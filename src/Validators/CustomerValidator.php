<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\BasicCustomer;
use IzzyPay\Models\DetailedCustomer;

class CustomerValidator
{
    /**
     * @param BasicCustomer $basicCustomer
     * @return array
     */
    public function validateBasicCustomer(AbstractCustomer $basicCustomer): array
    {
        $errors = [];

        if (trim($basicCustomer->getMerchantCustomerId()) === '') {
            $errors[] = 'merchantCustomerId';
        }

        if (!in_array($basicCustomer->getRegistered(), AbstractCustomer::ALLOWED_REGISTERED_VALUES)) {
            $errors[] = 'registered';
        }

        return $errors;
    }

    /**
     * @param DetailedCustomer $detailedCustomer
     * @return array
     */
    public function validateDetailedCustomer(DetailedCustomer $detailedCustomer): array
    {
        $errors = $this->validateBasicCustomer($detailedCustomer);

        if (trim($detailedCustomer->getName()) === '') {
            $errors[] = 'name';
        }

        if (trim($detailedCustomer->getSurname()) === '') {
            $errors[] = 'surname';
        }

        if (trim($detailedCustomer->getEmail()) === '') {
            $errors[] = 'email';
        }

        if (trim($detailedCustomer->getPhone()) === '') {
            $errors[] = 'phone';
        }

        return $errors;
    }
}
