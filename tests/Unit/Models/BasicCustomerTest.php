<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\BasicCustomer;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class BasicCustomerTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const REGISTERED = AbstractCustomer::REGISTERED_VALUE_GUEST;
    private const MERCHANT_CUSTOMER_ID = 'merchantCustomerId';
    private const OTHER = 'other';

    protected function setUp(): void
    {
        $this->fields = [
            'registered' => AbstractCustomer::REGISTERED_VALUE_MERCHANT,
            'merchantCustomerId' => 'change merchantCustomerId',
            'other' => 'change other',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $basicCustomer = $this->invokeConstructor(BasicCustomer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER]);
        $this->_testSettersAndGetters($basicCustomer);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $basicCustomer = $this->invokeConstructor(BasicCustomer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER]);
        $basicCustomerAsArray = $basicCustomer->toArray();
        $this->assertEqualsCanonicalizing([
            'registered' => self::REGISTERED,
            'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
            'other' => self::OTHER,
        ], $basicCustomerAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidCustomerException::class);
        BasicCustomer::create('invalid', '', '');
    }

    /**
     * @throws InvalidCustomerException
     */
    public function testCreate(): void
    {
        $basicCustomer = BasicCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $this->assertEquals(self::REGISTERED, $basicCustomer->getRegistered());
        $this->assertEquals(self::MERCHANT_CUSTOMER_ID, $basicCustomer->getMerchantCustomerId());
        $this->assertEquals(self::OTHER, $basicCustomer->getOther());
    }
}
