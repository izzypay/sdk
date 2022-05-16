<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Validators\AddressValidator;
use IzzyPay\Validators\CustomerValidator;

class DetailedCustomer extends AbstractCustomer
{
    private string $name;
    private string $surname;
    private string $phone;
    private string $email;
    private Address $deliveryAddress;
    private Address $invoiceAddress;

    /**
     * @param string $registered
     * @param string $merchantCustomerId
     * @param string $other
     * @param string $name
     * @param string $surname
     * @param string $phone
     * @param string $email
     * @param Address $deliveryAddress
     * @param Address $invoiceAddress
     */
    private function __construct(string $registered, string $merchantCustomerId, string $other, string $name, string $surname, string $phone, string $email, Address $deliveryAddress, Address $invoiceAddress)
    {
        parent::__construct($registered, $merchantCustomerId, $other);
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->phone = $phone;
        $this->deliveryAddress = $deliveryAddress;
        $this->invoiceAddress = $invoiceAddress;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DetailedCustomer
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     * @return DetailedCustomer
     */
    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return DetailedCustomer
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return DetailedCustomer
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Address
     */
    public function getDeliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    /**
     * @param Address $deliveryAddress
     * @return DetailedCustomer
     */
    public function setDeliveryAddress(Address $deliveryAddress): self
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    /**
     * @return Address
     */
    public function getInvoiceAddress(): Address
    {
        return $this->invoiceAddress;
    }

    /**
     * @param Address $invoiceAddress
     * @return DetailedCustomer
     */
    public function setInvoiceAddress(Address $invoiceAddress): self
    {
        $this->invoiceAddress = $invoiceAddress;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arrayData = parent::toArray();
        $arrayData['name'] = $this->name;
        $arrayData['surname'] = $this->surname;
        $arrayData['phone'] = $this->phone;
        $arrayData['email'] = $this->email;
        $arrayData['deliveryAddress'] = $this->deliveryAddress->toArray();
        $arrayData['invoiceAddress'] = $this->invoiceAddress->toArray();
        return $arrayData;
    }

    /**
     * @param string $registered
     * @param string $merchantCustomerId
     * @param string $other
     * @param string $name
     * @param string $surname
     * @param string $phone
     * @param string $email
     * @param Address $deliveryAddress
     * @param Address $invoiceAddress
     * @return static
     * @throws InvalidCustomerException
     */
    public static function create(string $registered, string $merchantCustomerId, string $other, string $name, string $surname, string $phone, string $email, Address $deliveryAddress, Address $invoiceAddress): self
    {
        $detailedCustomer = new DetailedCustomer($registered, $merchantCustomerId, $other, $name, $surname, $phone, $email, $deliveryAddress, $invoiceAddress);

        $addressValidator = new AddressValidator();
        $invalidDeliveryAddressFields = $addressValidator->validateAddress($deliveryAddress);
        $invalidInvoiceAddressFields = $addressValidator->validateAddress($invoiceAddress);
        $customerValidator = new CustomerValidator();
        $invalidCustomerFields = $customerValidator->validateDetailedCustomer($detailedCustomer);

        $invalidFields = array_merge($invalidDeliveryAddressFields, $invalidInvoiceAddressFields, $invalidCustomerFields);
        if (count($invalidFields) > 0) {
            throw new InvalidCustomerException($invalidFields);
        }

        return $detailedCustomer;
    }
}
