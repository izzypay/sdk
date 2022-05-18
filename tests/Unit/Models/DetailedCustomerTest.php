<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Address;
use IzzyPay\Models\DetailedCustomer;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DetailedCustomerTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const REGISTERED = AbstractCustomer::REGISTERED_VALUE_GUEST;
    private const MERCHANT_CUSTOMER_ID = 'merchantCustomerId';
    private const OTHER = 'other';
    private const NAME = 'name';
    private const SURNAME = 'surname';
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
        $detailedCustomer = $this->invokeConstructor(DetailedCustomer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::PHONE, self::EMAIL, $address, $address]);
        $this->_testSettersAndGetters($detailedCustomer);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        $detailedCustomer = $this->invokeConstructor(DetailedCustomer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::PHONE, self::EMAIL, $address, $address]);
        $detailedCustomerAsArray = $detailedCustomer->toArray();
        $this->assertEqualsCanonicalizing([
            'registered' => self::REGISTERED,
            'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
            'other' => self::OTHER,
            'name' => self::NAME,
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
        ], $detailedCustomerAsArray);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateWithException(): void
    {
        $this->expectException(InvalidCustomerException::class);
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        DetailedCustomer::create('invalid', '', '', '', '', '', '', $address, $address);
    }

    /**
     * @throws InvalidCustomerException
     * @throws ReflectionException
     */
    public function testCreate(): void
    {
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        $detailedCustomer = DetailedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::PHONE, self::EMAIL, $address, $address);
        $this->assertEquals(self::REGISTERED, $detailedCustomer->getRegistered());
        $this->assertEquals(self::MERCHANT_CUSTOMER_ID, $detailedCustomer->getMerchantCustomerId());
        $this->assertEquals(self::OTHER, $detailedCustomer->getOther());
        $this->assertEquals(self::NAME, $detailedCustomer->getName());
        $this->assertEquals(self::SURNAME, $detailedCustomer->getSurname());
        $this->assertEquals(self::PHONE, $detailedCustomer->getPhone());
        $this->assertEquals(self::EMAIL, $detailedCustomer->getEmail());
        $this->assertEquals($address, $detailedCustomer->getDeliveryAddress());
        $this->assertEquals($address, $detailedCustomer->getInvoiceAddress());
    }
}
