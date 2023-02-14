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
        $invalidOther1 = $this->invokeConstructor(Other::class, ['']);
        $validOther1 = $this->invokeConstructor(Other::class, ['Chrome']);
        return [
            [$invalidOther1, InvalidOtherException::class],
            [$validOther1, null],
        ];
    }
}
