<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models\Response;

use IzzyPay\Models\Response\ReturnResponse;
use PHPUnit\Framework\TestCase;

class ReturnResponseTest extends TestCase
{
    private const RETURN_DATE = '2022-04-04T12:34:56+0010';
    private const REDUCED_VALUE = 100.2;

    public function testGetters(): void
    {
        $startResponse = new ReturnResponse(self::RETURN_DATE, self::REDUCED_VALUE);
        $this->assertEquals(self::RETURN_DATE, $startResponse->getReturnDate());
        $this->assertEquals(self::REDUCED_VALUE, $startResponse->getReducedValue());
    }
}
