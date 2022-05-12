<?php

declare(strict_types=1);

namespace Bnpl\Validators;

use Bnpl\Models\Address;

class AddressValidator {
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
     * @return array
     */
    public function validateAddress(Address $address): array
    {
        $errors = [];

        if (!$address->getZip() || !$this->validateZipCode($address->getZip())) {
            $errors[] = 'zip';
        }

        if (!$address->getCity() || trim($address->getCity()) === '') {
            $errors[] = 'city';
        }

        $hasSeparateStreetAddress = (trim($address->getStreet()) !== '') && (trim($address->getHouseNo()) !== '');
        if (!($hasSeparateStreetAddress || (trim($address->getAddress1()) !== ''))) {
            $errors[] = 'street';
            $errors[] = 'houseNo';
            $errors[] = 'address1';
        }

        return $errors;
    }
}
