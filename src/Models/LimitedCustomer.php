<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Validators\CustomerValidator;

class LimitedCustomer extends AbstractCustomer
{
    /**
     * @param string $registered
     * @param string|null $merchantCustomerId
     * @param string $other
     * @return static
     * @throws InvalidCustomerException
     */
    public static function create(string $registered, ?string $merchantCustomerId, string $other): self
    {
        $basicCustomer = new LimitedCustomer($registered, $merchantCustomerId, $other);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateLimitedCustomer($basicCustomer);

        return $basicCustomer;
    }
}
