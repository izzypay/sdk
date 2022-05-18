<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Models\Urls;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\UrlsValidator;
use PHPUnit\Framework\TestCase;

class UrlsValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getUrlsProvider
     */
    public function testUrlsValidator(Urls $urls, array $expected): void
    {
        $urlsValidator = new UrlsValidator();
        $errors = $urlsValidator->validateUrls($urls);
        $this->assertEquals($expected, $errors);
    }

    public function getUrlsProvider(): array
    {
        $invalidUrls = $this->invokeConstructor(Urls::class, ['']);
        $validUrls = $this->invokeConstructor(Urls::class, ['https://example.com']);
        return [
            [$invalidUrls, ['ipn']],
            [$validUrls, []],
        ];
    }
}
