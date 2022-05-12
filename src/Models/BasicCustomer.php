<?php

declare(strict_types=1);

namespace Bnpl\Models;

use Bnpl\Exception\InvalidCustomerException;
use Bnpl\Validators\CustomerValidator;

class BasicCustomer extends AbstractCustomer
{
    /**
     * @param string $registered
     * @param string $merchantCustomerId
     * @param string $other
     * @return static
     * @throws InvalidCustomerException
     */
    public static function create(string $registered, string $merchantCustomerId, string $other): self
    {
        $basicCustomer = new BasicCustomer($registered, $merchantCustomerId, $other);

        $customerValidator = new CustomerValidator();
        $invalidFields = $customerValidator->validateBasicCustomer($basicCustomer);
        if (count($invalidFields) > 0) {
            throw new InvalidCustomerException($invalidFields);
        }

        return $basicCustomer;
    }
}
