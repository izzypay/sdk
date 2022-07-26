<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Customer;

class CustomerValidator
{
    /**
     * @param AbstractCustomer $limitedCustomer
     * @return void
     * @throws InvalidCustomerException
     */
    public function validateLimitedCustomer(AbstractCustomer $limitedCustomer): void
    {
        $errors = [];

        if (trim($limitedCustomer->getMerchantCustomerId()) === '') {
            $errors[] = 'merchantCustomerId';
        }

        if (!in_array($limitedCustomer->getRegistered(), AbstractCustomer::ALLOWED_REGISTERED_VALUES)) {
            $errors[] = 'registered';
        }

        if (count($errors) > 0) {
            throw new InvalidCustomerException($errors);
        }
    }

    /**
     * @param Customer $detailedCustomer
     * @return void
     * @throws InvalidCustomerException
     * @throws InvalidAddressException
     */
    public function validateCustomer(Customer $detailedCustomer): void
    {
        $this->validateLimitedCustomer($detailedCustomer);

        $addressValidator = new AddressValidator();
        $addressValidator->validateAddress($detailedCustomer->getDeliveryAddress());
        $addressValidator->validateAddress($detailedCustomer->getInvoiceAddress());

        $errors = [];

        if (trim($detailedCustomer->getName()) === '') {
            $errors[] = 'name';
        }

        if (trim($detailedCustomer->getSurname()) === '') {
            $errors[] = 'surname';
        }

        if (!filter_var($detailedCustomer->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'email';
        }

        if (trim($detailedCustomer->getPhone()) === '') {
            $errors[] = 'phone';
        }

        if (count($errors) > 0) {
            throw new InvalidCustomerException($errors);
        }
    }
}
