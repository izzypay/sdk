<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Models\Address;

class AddressValidator
{
    /**
     * @param string $zipCode
     * @return bool
     */
    public function validateZipCode(string $zipCode): bool
    {
        return strlen(trim($zipCode)) === 4;
    }

    /**
     * @param Address $address
     * @return void
     * @throws InvalidAddressException
     */
    public function validateAddress(Address $address): void
    {
        $errors = [];

        if (!$this->validateZipCode($address->getZip())) {
            $errors[] = 'zip';
        }

        if (trim($address->getCity()) === '') {
            $errors[] = 'city';
        }
        if (($address->getStreet() !== null) && (trim($address->getStreet()) === '')) {
            $errors[] = 'street';
        }
        if (($address->getHouseNo() !== null) && (trim($address->getHouseNo()) === '')) {
            $errors[] = 'houseNo';
        }
        if (($address->getAddress1() !== null) && (trim($address->getAddress1()) === '')) {
            $errors[] = 'address1';
        }
        if (($address->getAddress2() !== null) && (trim($address->getAddress2()) === '')) {
            $errors[] = 'address2';
        }
        if (($address->getAddress3() !== null) && (trim($address->getAddress3()) === '')) {
            $errors[] = 'address3';
        }

        $hasSeparateStreetAddress = (trim($address->getStreet()) !== '') && (trim($address->getHouseNo()) !== '');
        if (!($hasSeparateStreetAddress || (trim($address->getAddress1()) !== ''))) {
            $errors[] = 'street';
            $errors[] = 'houseNo';
            $errors[] = 'address1';
        }

        if (count($errors) > 0) {
            throw new InvalidAddressException($errors);
        }
    }
}
