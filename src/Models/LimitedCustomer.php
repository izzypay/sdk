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
     * @param string|null $companyName
     * @param string|null $other
     * @return static
     * @throws InvalidCustomerException
     */
    public static function create(string $registered, ?string $merchantCustomerId = null, ?string $companyName = null, ?string $other = null): self
    {
        $basicCustomer = new LimitedCustomer($registered, $merchantCustomerId, $companyName, $other);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateLimitedCustomer($basicCustomer);

        return $basicCustomer;
    }
}
