<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Address;
use IzzyPay\Models\BasicCustomer;
use IzzyPay\Models\DetailedCustomer;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\CustomerValidator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CustomerValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getBasicCustomerProvider
     */
    public function testValidateBasicCustomer(BasicCustomer $basicCustomer, array $expected): void
    {
        $customerValidator = new CustomerValidator();
        $errors = $customerValidator->validateBasicCustomer($basicCustomer);
        $this->assertEqualsCanonicalizing($expected, $errors);
    }

    /**
     * @dataProvider getDetailedCustomerProvider
     */
    public function testValidateDetailedCustomer(DetailedCustomer $detailedCustomer, array $expected): void
    {
        $customerValidator = new CustomerValidator();
        $errors = $customerValidator->validateDetailedCustomer($detailedCustomer);
        $this->assertEqualsCanonicalizing($expected, $errors);
    }

    /**
     * @throws ReflectionException
     */
    public function getBasicCustomerProvider(): array
    {
        $invalidBasicCustomer1 = $this->invokeConstructor(BasicCustomer::class, [' ', '', '']);
        $invalidBasicCustomer2 = $this->invokeConstructor(BasicCustomer::class, ['invalid', ' ', '']);
        $validBasicCustomer1 = $this->invokeConstructor(BasicCustomer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', '']);
        $validBasicCustomer2 = $this->invokeConstructor(BasicCustomer::class, [AbstractCustomer::REGISTERED_VALUE_MERCHANT, 'merchantCustomerId', '']);
        $validBasicCustomer3 = $this->invokeConstructor(BasicCustomer::class, [AbstractCustomer::REGISTERED_VALUE_3RDPARTY, 'merchantCustomerId', '']);
        $validBasicCustomer4 = $this->invokeConstructor(BasicCustomer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', 'other']);
        return [
            [$invalidBasicCustomer1, ['merchantCustomerId', 'registered']],
            [$invalidBasicCustomer2, ['merchantCustomerId', 'registered']],
            [$validBasicCustomer1, []],
            [$validBasicCustomer2, []],
            [$validBasicCustomer3, []],
            [$validBasicCustomer4, []]
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function getDetailedCustomerProvider(): array
    {
        $invalidAddress = $this->invokeConstructor(Address::class, [' ', ' ', '', '', '', '', '']);
        $validAddress = $this->invokeConstructor(Address::class, ['1234', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3']);
        $invalidDetailedCustomer1 = $this->invokeConstructor(DetailedCustomer::class, [' ', ' ', '', ' ', ' ', ' ', ' ', $invalidAddress, $invalidAddress]);
        $invalidDetailedCustomer2 = $this->invokeConstructor(DetailedCustomer::class, [' ', ' ', '', ' ', ' ', ' ', ' ', $invalidAddress, $validAddress]);
        $invalidDetailedCustomer3 = $this->invokeConstructor(DetailedCustomer::class, [' ', ' ', '', ' ', ' ', ' ', ' ', $validAddress, $invalidAddress]);
        $invalidDetailedCustomer4 = $this->invokeConstructor(DetailedCustomer::class, [' ', ' ', '', ' ', ' ', ' ', ' ', $validAddress, $validAddress]);
        $invalidDetailedCustomer5 = $this->invokeConstructor(DetailedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', 'other', 'name', 'surname', 'phone', 'email', $validAddress, $validAddress]);
        $validDetailedCustomer = $this->invokeConstructor(DetailedCustomer::class, [AbstractCustomer::REGISTERED_VALUE_GUEST, 'merchantCustomerId', 'other', 'name', 'surname', 'phone', 'email@example.com', $validAddress, $validAddress]);
        return [
            [$invalidDetailedCustomer1, ['zip', 'city', 'street', 'houseNo', 'address1', 'zip', 'city', 'street', 'houseNo', 'address1', 'merchantCustomerId', 'registered', 'name', 'surname', 'email', 'phone']],
            [$invalidDetailedCustomer2, ['zip', 'city', 'street', 'houseNo', 'address1', 'merchantCustomerId', 'registered', 'name', 'surname', 'email', 'phone']],
            [$invalidDetailedCustomer3, ['zip', 'city', 'street', 'houseNo', 'address1', 'merchantCustomerId', 'registered', 'name', 'surname', 'email', 'phone']],
            [$invalidDetailedCustomer4, ['merchantCustomerId', 'registered', 'name', 'surname', 'email', 'phone']],
            [$invalidDetailedCustomer5, ['email']],
            [$validDetailedCustomer, []],
        ];
    }
}
