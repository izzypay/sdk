<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class LimitedCustomerTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const REGISTERED = AbstractCustomer::REGISTERED_VALUE_GUEST;
    private const MERCHANT_CUSTOMER_ID = 'merchantCustomerId';
    private const COMPANY_NAME = 'company name';
    private const OTHER = 'other';

    protected function setUp(): void
    {
        $this->fields = [
            'registered' => AbstractCustomer::REGISTERED_VALUE_MERCHANT,
            'merchantCustomerId' => 'change merchantCustomerId',
            'companyName' => 'change company name',
            'other' => 'change other',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $limitedCustomer = $this->invokeConstructor(LimitedCustomer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER]);
        $this->_testSettersAndGetters($limitedCustomer);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $limitedCustomer = $this->invokeConstructor(LimitedCustomer::class, [self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER]);
        $limitedCustomerAsArray = $limitedCustomer->toArray();
        $this->assertEqualsCanonicalizing([
            'registered' => self::REGISTERED,
            'merchantCustomerId' => self::MERCHANT_CUSTOMER_ID,
            'companyName' => self::COMPANY_NAME,
            'other' => self::OTHER,
        ], $limitedCustomerAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidCustomerException::class);
        LimitedCustomer::create('invalid', '', '', '');
    }

    /**
     * @throws InvalidCustomerException
     */
    public function testCreate(): void
    {
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER);
        $this->assertEquals(self::REGISTERED, $limitedCustomer->getRegistered());
        $this->assertEquals(self::MERCHANT_CUSTOMER_ID, $limitedCustomer->getMerchantCustomerId());
        $this->assertEquals(self::COMPANY_NAME, $limitedCustomer->getCompanyName());
        $this->assertEquals(self::OTHER, $limitedCustomer->getOther());
    }
}
