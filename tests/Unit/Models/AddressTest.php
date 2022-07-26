<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Models\Address;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class AddressTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const ZIP = '1234';
    private const CITY = 'city';
    private const STREET = 'street';
    private const HOUSE_NO = 'houseNo';
    private const ADDRESS1 = 'address1';
    private const ADDRESS2 = 'address2';
    private const ADDRESS3 = 'address3';

    protected function setUp(): void
    {
        $this->fields = [
            'zip' => '4321',
            'city' => 'ct',
            'street' => 'str',
            'houseNo' => 'house number',
            'address1' => 'address 1',
            'address2' => 'address 2',
            'address3' => 'address 3',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        $this->_testSettersAndGetters($address);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $address = $this->invokeConstructor(Address::class, [self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3]);
        $urlsAsArray = $address->toArray();
        $this->assertEqualsCanonicalizing([
            'zip' => self::ZIP,
            'city' => self::CITY,
            'street' => self::STREET,
            'houseNo' => self::HOUSE_NO,
            'address1' => self::ADDRESS1,
            'address2' => self::ADDRESS2,
            'address3' => self::ADDRESS3,
        ], $urlsAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidAddressException::class);
        Address::create('', '', '', '', '', '', '');
    }

    /**
     * @throws InvalidAddressException
     */
    public function testCreate(): void
    {
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $this->assertEquals(self::ZIP, $address->getZip());
        $this->assertEquals(self::CITY, $address->getCity());
        $this->assertEquals(self::STREET, $address->getStreet());
        $this->assertEquals(self::HOUSE_NO, $address->getHouseNo());
        $this->assertEquals(self::ADDRESS1, $address->getAddress1());
        $this->assertEquals(self::ADDRESS2, $address->getAddress2());
        $this->assertEquals(self::ADDRESS3, $address->getAddress3());
    }
}
