<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Exceptions\InvalidAddressException;
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
     * @throws InvalidAddressException
     */
    public function testValidateAddress(Address $address, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $addressValidator = new AddressValidator();
        $addressValidator->validateAddress($address);
        if (!$exception) {
            $this->assertTrue(true);
        }
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
        $validAddress1 = $this->invokeConstructor(Address::class, ['1234', 'city', 'street', 'houseNo', null, null, null]);
        $validAddress2 = $this->invokeConstructor(Address::class, ['1234', 'city', null, null, 'address1', null, null]);
        $validAddress3 = $this->invokeConstructor(Address::class, ['1234', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3']);
        return [
            [$invalidAddress, InvalidAddressException::class],
            [$validAddress1, null],
            [$validAddress2, null],
            [$validAddress3, null],
        ];
    }
}
