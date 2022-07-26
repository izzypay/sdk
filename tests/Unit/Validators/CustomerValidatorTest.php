<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Address;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Customer;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\CustomerValidator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CustomerValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getLimitedCustomerProvider
     */
    public function testValidateLimitedCustomer(LimitedCustomer $limitedCustomer, bool $throwsException): void
    {
        if ($throwsException) {
            $this->expectException(InvalidCustomerException::class);
        }
        $customerValidator = new CustomerValidator();
        $customerValidator->validateLimitedCustomer($limitedCustomer);
        if (!$throwsException) {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getCustomerProvider
     */
    public function testValidateCustomer(Customer $customer, bool $throwsException): void
    {
        if ($throwsException) {
            $this->expectException(InvalidCustomerException::class);
        }
        $customerValidator = new CustomerValidator();
        $customerValidator->validateCustomer($customer);
        if (!$throwsException) {
            $this->assertTrue(true);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function getLimitedCustomerProvider(): array
    {
        $invalidLimitedCustomer1 = $this->invokeConstructor(LimitedCustomer::class, [' ', '', '']);
        $invalidLimitedCustomer2 = $this->invokeConstructor(LimitedCustomer::class, ['invalid', ' ', '']);
        $invalidLimitedCustomer3 = $this->invokeConstructor(LimitedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_MERCHANT, null, '']);
        $invalidLimitedCustomer4 = $this->invokeConstructor(LimitedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_3RDPARTY, null, '']);
        $validLimitedCustomer1 = $this->invokeConstructor(LimitedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', '']);
        $validLimitedCustomer2 = $this->invokeConstructor(LimitedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_MERCHANT, 'merchantCustomerId', '']);
        $validLimitedCustomer3 = $this->invokeConstructor(LimitedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_3RDPARTY, 'merchantCustomerId', '']);
        $validLimitedCustomer4 = $this->invokeConstructor(LimitedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', 'other']);
        $validLimitedCustomer5 = $this->invokeConstructor(LimitedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, null, '']);
        return [
            [$invalidLimitedCustomer1, true],
            [$invalidLimitedCustomer2, true],
            [$invalidLimitedCustomer3, true],
            [$invalidLimitedCustomer4, true],
            [$validLimitedCustomer1, false],
            [$validLimitedCustomer2, false],
            [$validLimitedCustomer3, false],
            [$validLimitedCustomer4, false],
            [$validLimitedCustomer5, false],
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function getCustomerProvider(): array
    {
        $invalidAddress = $this->invokeConstructor(Address::class, [' ', ' ', '', '', '', '', '']);
        $validAddress = $this->invokeConstructor(Address::class, ['1234', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3']);
        $invalidCustomer1 = $this->invokeConstructor(Customer::class, [' ', ' ', '', ' ', ' ', '', ' ', ' ', $invalidAddress, $invalidAddress]);
        $invalidCustomer2 = $this->invokeConstructor(Customer::class, [' ', ' ', '', ' ', ' ', '', ' ', ' ', $invalidAddress, $validAddress]);
        $invalidCustomer3 = $this->invokeConstructor(Customer::class, [' ', ' ', '', ' ', ' ', '',' ', ' ', $validAddress, $invalidAddress]);
        $invalidCustomer4 = $this->invokeConstructor(Customer::class, [' ', ' ', '', ' ', ' ', '',' ', ' ', $validAddress, $validAddress]);
        $invalidCustomer5 = $this->invokeConstructor(Customer::class, [AbstractCustomer::REGISTERED_VALUE_MERCHANT, null, 'other', 'name', 'surname', 'company name', 'phone', 'email', $validAddress, $validAddress]);
        $invalidCustomer6 = $this->invokeConstructor(Customer::class, [AbstractCustomer::REGISTERED_VALUE_3RDPARTY, null, 'other', 'name', 'surname', 'company name', 'phone', 'email', $validAddress, $validAddress]);
        $invalidCustomer7 = $this->invokeConstructor(Customer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', 'other', 'name', 'surname', 'company name', 'phone', 'email', $validAddress, $validAddress]);
        $validCustomer1 = $this->invokeConstructor(Customer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, null, 'other', 'name', 'surname', 'company name', 'phone', 'email@example.com', $validAddress, $validAddress]);
        $validCustomer2 = $this->invokeConstructor(Customer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', 'other', 'name', 'surname', 'company name', 'phone', 'email@example.com', $validAddress, $validAddress]);
        return [
            [$invalidCustomer1, true],
            [$invalidCustomer2, true],
            [$invalidCustomer3, true],
            [$invalidCustomer4, true],
            [$invalidCustomer5, true],
            [$invalidCustomer6, true],
            [$invalidCustomer7, true],
            [$validCustomer1, false],
            [$validCustomer2, false],
        ];
    }
}
