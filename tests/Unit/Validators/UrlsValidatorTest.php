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
     * @return array<int, array<int, object|string|null>>
     * @throws ReflectionException
     */
    public function getUrlsProvider(): array
    {
        $invalidUrls1 = $this->invokeConstructor(Urls::class, ['', '']);
        $invalidUrls2 = $this->invokeConstructor(Urls::class, ['invalid', '']);
        $invalidUrls3 = $this->invokeConstructor(Urls::class, ['https://ipn.com', '']);
        $invalidUrls4 = $this->invokeConstructor(Urls::class, ['https://ipn.com', 'invalid']);
        $validUrls1 = $this->invokeConstructor(Urls::class, ['https://ipn.com', '']);
        $validUrls2 = $this->invokeConstructor(Urls::class, ['https://ipn.com', 'https://checkout.com']);
        return [
            [$invalidUrls1, InvalidUrlsException::class],
            [$invalidUrls2, InvalidUrlsException::class],
            [$invalidUrls3, InvalidUrlsException::class],
            [$invalidUrls4, InvalidUrlsException::class],
            [$validUrls1, InvalidUrlsException::class],
            [$validUrls2, null],
        ];
    }
}
