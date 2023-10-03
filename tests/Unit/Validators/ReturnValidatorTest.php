<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Exceptions\InvalidReturnDataException;
use IzzyPay\Validators\ReturnValidator;
use PHPUnit\Framework\TestCase;

class ReturnValidatorTest extends TestCase
{
    /**
     * @dataProvider getReturnDataProvider
     * @throws InvalidReturnDataException
     */
    public function testValidate(?float $reducedValue, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $returnValidator = new ReturnValidator();
        $returnValidator->validate($reducedValue);
        if (!$exception) {
            $this->assertTrue(true);
        }
    }

    public function getReturnDataProvider(): array
    {
        return [
            [-100.2, InvalidReturnDataException::class],
            [null, null],
            [100.2, null],
        ];
    }
}
