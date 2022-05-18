<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

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
     */
    public function testValidateUrls(Urls $urls, array $expected): void
    {
        $urlsValidator = new UrlsValidator();
        $errors = $urlsValidator->validateUrls($urls);
        $this->assertEqualsCanonicalizing($expected, $errors);
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
            [$invalidUrls1, ['ipn']],
            [$invalidUrls2, ['ipn']],
            [$validUrls, []],
        ];
    }
}
