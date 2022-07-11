<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Validators\CustomerValidator;

class Customer extends AbstractCustomer
{
    private string $name;
    private string $surname;
    private string $companyName;
    private string $phone;
    private string $email;
    private Address $deliveryAddress;
    private Address $invoiceAddress;

    /**
     * @param string $registered
     * @param string $merchantCustomerId
     * @param string $other
     * @param string $name
     * @param string $companyName
     * @param string $surname
     * @param string $phone
     * @param string $email
     * @param Address $deliveryAddress
     * @param Address $invoiceAddress
     */
    private function __construct(string $registered, string $merchantCustomerId, string $other, string $name, string $companyName, string $surname, string $phone, string $email, Address $deliveryAddress, Address $invoiceAddress)
    {
        parent::__construct($registered, $merchantCustomerId, $other);
        $this->name = $name;
        $this->companyName = $companyName;
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
     * @return Customer
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
     * @return Customer
     */
    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     * @return Customer
     */
    public function setCompanyName(string $companyName): Customer
    {
        $this->companyName = $companyName;
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
     * @return Customer
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
     * @return Customer
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
     * @return Customer
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
     * @return Customer
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
        $arrayData['companyName'] = $this->companyName;
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
     * @param string $companyName
     * @param string $phone
     * @param string $email
     * @param Address $deliveryAddress
     * @param Address $invoiceAddress
     * @return static
     * @throws InvalidCustomerException
     * @throws InvalidAddressException
     */
    public static function create(string $registered, string $merchantCustomerId, string $other, string $name, string $surname, string $companyName, string $phone, string $email, Address $deliveryAddress, Address $invoiceAddress): self
    {
        $detailedCustomer = new Customer($registered, $merchantCustomerId, $other, $name, $companyName, $surname, $phone, $email, $deliveryAddress, $invoiceAddress);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateCustomer($detailedCustomer);

        return $detailedCustomer;
    }
}
