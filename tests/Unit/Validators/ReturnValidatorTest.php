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
    public function testValidate(string $returnDate, ?float $reducedValue, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $returnValidator = new ReturnValidator();
        $returnValidator->validate($returnDate, $reducedValue);
        if (!$exception) {
            $this->assertTrue(true);
        }
    }

    public function getReturnDataProvider(): array
    {
        return [
            ['', null, InvalidReturnDataException::class],
            ['invalid', null, InvalidReturnDataException::class],
            ['2022-04-04T12:34:56', null, InvalidReturnDataException::class],
            ['2022-04-04T12:34:56+0010', -100.2, InvalidReturnDataException::class],
            ['2022-04-04T12:34:56+0010', null, null],
            ['2022-04-04T12:34:56+0010', 100.2, null],
        ];
    }
}
