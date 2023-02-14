<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Validators\AddressValidator;

class Address
{
    private string $zip;
    private string $city;
    private ?string $street;
    private ?string $houseNo;
    private ?string $address1;
    private ?string $address2;
    private ?string $address3;

    /**
     * @param string $zip
     * @param string $city
     * @param string|null $street
     * @param string|null $houseNo
     * @param string|null $address1
     * @param string|null $address2
     * @param string|null $address3
     */
    private function __construct(string $zip, string $city, ?string $street, ?string $houseNo, ?string $address1, ?string $address2, ?string $address3)
    {
        $this->zip = $zip;
        $this->city = $city;
        $this->street = $street;
        $this->houseNo = $houseNo;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->address3 = $address3;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     * @return Address
     */
    public function setZip(string $zip): self
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Address
     */
    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     * @return Address
     */
    public function setStreet(?string $street): self
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHouseNo(): ?string
    {
        return $this->houseNo;
    }

    /**
     * @param string|null $houseNo
     * @return Address
     */
    public function setHouseNo(?string $houseNo): self
    {
        $this->houseNo = $houseNo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    /**
     * @param string|null $address1
     * @return Address
     */
    public function setAddress1(?string $address1): self
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * @param string|null $address2
     * @return Address
     */
    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    /**
     * @param string|null $address3
     * @return Address
     */
    public function setAddress3(?string $address3): self
    {
        $this->address3 = $address3;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'zip' => $this->zip,
            'city' => $this->city,
        ];
        if (!is_null($this->street)) {
            $data['street'] = $this->street;
        }
        if (!is_null($this->houseNo)) {
            $data['houseNo'] = $this->houseNo;
        }
        if (!is_null($this->address1)) {
            $data['address1'] = $this->address1;
        }
        if (!is_null($this->address2)) {
            $data['address2'] = $this->address2;
        }
        if (!is_null($this->address3)) {
            $data['address3'] = $this->address3;
        }
        return $data;
    }

    /**
     * @param string $zip
     * @param string $city
     * @param string|null $street
     * @param string|null $houseNo
     * @param string|null $address1
     * @param string|null $address2
     * @param string|null $address3
     * @return Address
     * @throws InvalidAddressException
     */
    public static function create(string $zip, string $city, ?string $street, ?string $houseNo, ?string $address1, ?string $address2, ?string $address3): self
    {
        $address = new Address($zip, $city, $street, $houseNo, $address1, $address2, $address3);

        $addressValidator = new AddressValidator();
        $addressValidator->validateAddress($address);

        return $address;
    }
}
