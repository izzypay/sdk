<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

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
     */
    public function testValidateOther(Other $other, array $expected): void
    {
        $otherValidator = new OtherValidator();
        $errors = $otherValidator->validateOther($other);
        $this->assertEqualsCanonicalizing($expected, $errors);
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
            [$invalidOther1, ['ip']],
            [$invalidOther2, ['ip']],
            [$validOther1, []],
            [$validOther2, []],
        ];
    }
}
