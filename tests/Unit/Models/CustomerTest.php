<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Address;
use IzzyPay\Models\Customer;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CustomerTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const REGISTERED = AbstractCustomer::REGISTERED_VALUE_GUEST;
    private const MERCHANT_CUSTOMER_ID = 'merchantCustomerId';
    private const OTHER = 'other';
    private const NAME = 'name';
    private const SURNAME = 'surname';
    private const COMPANY_NAME = 'company name';
    private const PHONE = '1234567890';
    private const EMAIL = 'email@example.com';
    private const ZIP = '1234';
    private const CITY = 'city';
    private const STREET = 'street';
    private const HOUSE_NO = 'houseNo';
    private const ADDRESS1 = 'address1';
    private const ADDRESS2 = 'address2';
    private const ADDRESS3 = 'address3';

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->fields = [
            'registered' => AbstractCustomer::REGISTERED_VALUE_MERCHANT,
            'merchantCustomerId' => 'change merchantCustomerId',
            'other' => 'change other',
            'name' => 'change name',
            'companyName' => 'change company name',
            'surname' => 'change surname',
            'phone' => 'change phone',
            'email' => 'change email',
            'deliveryAddress' => $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]),
            'invoiceAddress' => $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]),
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        $customer = $this->invokeConstructor(Customer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address]);
        $this->_testSettersAndGetters($customer);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        $customer = $this->invokeConstructor(Customer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address]);
        $customerAsArray = $customer->toArray();
        $this->assertEqualsCanonicalizing([
            'registered' => self::REGISTERED,
            'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
            'other' => self::OTHER,
            'name' => self::NAME,
            'companyName' => self::COMPANY_NAME,
            'surname' => self::SURNAME,
            'phone' => self::PHONE,
            'email' => self::EMAIL,
            'deliveryAddress' => [
                'zip' => self::ZIP,
                'city' => self::CITY,
                'street' => self::STREET,
                'houseNo' => self::HOUSE_NO,
                'address1' => self::ADDRESS1,
                'address2' => self::ADDRESS2,
                'address3' => self::ADDRESS3,
            ],
            'invoiceAddress' => [
                'zip' => self::ZIP,
                'city' => self::CITY,
                'street' => self::STREET,
                'houseNo' => self::HOUSE_NO,
                'address1' => self::ADDRESS1,
                'address2' => self::ADDRESS2,
                'address3' => self::ADDRESS3,
            ],
        ], $customerAsArray);
    }

    /**
     * @throws InvalidAddressException
     * @throws ReflectionException
     */
    public function testCreateWithException(): void
    {
        $this->expectException(InvalidCustomerException::class);
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        Customer::create('invalid', '', '', '', '', '','', '', $address, $address);
    }

    /**
     * @throws InvalidAddressException
     * @throws InvalidCustomerException
     * @throws ReflectionException
     */
    public function testCreate(): void
    {
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME,self::OTHER, self::NAME, self::SURNAME, self::PHONE, self::EMAIL, $address, $address);
        $this->assertEquals(self::REGISTERED, $customer->getRegistered());
        $this->assertEquals(self::MERCHANT_CUSTOMER_ID, $customer->getMerchantCustomerId());
        $this->assertEquals(self::OTHER, $customer->getOther());
        $this->assertEquals(self::NAME, $customer->getName());
        $this->assertEquals(self::SURNAME, $customer->getSurname());
        $this->assertEquals(self::PHONE, $customer->getPhone());
        $this->assertEquals(self::EMAIL, $customer->getEmail());
        $this->assertEquals($address, $customer->getDeliveryAddress());
        $this->assertEquals($address, $customer->getInvoiceAddress());
    }
}
