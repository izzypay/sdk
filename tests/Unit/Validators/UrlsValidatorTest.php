<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Models\Urls;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\UrlsValidator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class UrlsValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getUrlsProvider
     * @throws InvalidUrlsException
     */
    public function testValidateUrls(Urls $urls, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $urlsValidator = new UrlsValidator();
        $urlsValidator->validateUrls($urls);
        if (!$exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function getUrlsProvider(): array
    {
        $invalidUrls1 = $this->invokeConstructor(Urls::class, ['']);
        $invalidUrls2 = $this->invokeConstructor(Urls::class, ['invalid']);
        $validUrls = $this->invokeConstructor(Urls::class, ['https://example.com']);
        return [
            [$invalidUrls1, InvalidUrlsException::class],
            [$invalidUrls2, InvalidUrlsException::class],
            [$validUrls, null],
        ];
    }
}
