<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Models\Address;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\AddressValidator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class AddressValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getZipCodeProvider
     */
    public function testValidateZipCode(string $zip, bool $expected): void
    {
        $addressValidator = new AddressValidator();
        $errors = $addressValidator->validateZipCode($zip);
        $this->assertEquals($expected, $errors);
    }

    /**
     * @dataProvider getAddressProvider
     */
    public function testValidateAddress(Address $address, array $expected): void
    {
        $addressValidator = new AddressValidator();
        $errors = $addressValidator->validateAddress($address);
        $this->assertEqualsCanonicalizing($expected, $errors);
    }

    public function getZipCodeProvider(): array
    {
        return [
            ['', false],
            ['invalid', false],
            [' 1234 ', true],
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function getAddressProvider(): array
    {
        $invalidAddress = $this->invokeConstructor(Address::class, [' ', ' ', '', '', '', '', '']);
        $validAddress1 = $this->invokeConstructor(Address::class, ['1234', 'city', 'street', 'houseNo', '', '', '']);
        $validAddress2 = $this->invokeConstructor(Address::class, ['1234', 'city', '', '', 'address1', '', '']);
        $validAddress3 = $this->invokeConstructor(Address::class, ['1234', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3']);
        return [
            [$invalidAddress, ['zip', 'city', 'street', 'houseNo', 'address1']],
            [$validAddress1, []],
            [$validAddress2, []],
            [$validAddress3, []],
        ];
    }
}
