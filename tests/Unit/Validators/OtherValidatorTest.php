<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Models\Other;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\OtherValidator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class OtherValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getOthersProvider
     * @throws InvalidOtherException
     */
    public function testValidateOther(Other $other, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $otherValidator = new OtherValidator();
        $otherValidator->validateOther($other);
        if (!$exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function getOthersProvider(): array
    {
        $invalidOther1 = $this->invokeConstructor(Other::class, ['', '', '']);
        $invalidOther2 = $this->invokeConstructor(Other::class, ['192.168.1.', '', '']);
        $validOther1 = $this->invokeConstructor(Other::class, ['192.168.1.1', '', '']);
        $validOther2 = $this->invokeConstructor(Other::class, ['192.168.1.1', 'Chrome', 'Linux']);
        return [
            [$invalidOther1, InvalidOtherException::class],
            [$invalidOther2, InvalidOtherException::class],
            [$validOther1, null],
            [$validOther2, null],
        ];
    }
}
