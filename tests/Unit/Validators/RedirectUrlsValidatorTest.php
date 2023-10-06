<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Models\RedirectUrls;
use IzzyPay\Models\Urls;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\RedirectUrlsValidator;
use IzzyPay\Validators\UrlsValidator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class RedirectUrlsValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getUrlsProvider
     * @throws InvalidUrlsException
     */
    public function testValidateUrls(RedirectUrls $urls, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $urlsValidator = new RedirectUrlsValidator();
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
        $invalidUrls1 = $this->invokeConstructor(
            RedirectUrls::class,
            ['', '', '', '', '']
        );
        $invalidUrls2 = $this->invokeConstructor(
            RedirectUrls::class,
            ['invalid', '', '', '', '']
        );
        $invalidUrls3 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', '', '', '', '']
        );
        $invalidUrls4 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'invalid', '', '', '']
        );
        $invalidUrls5 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'https://webshop.url/rejected', '', '', '']
        );
        $invalidUrls6 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'https://webshop.url/rejected', 'invalid', '', '']
        );
        $invalidUrls7 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'https://webshop.url/rejected', 'https://webshop.url/cancelled', '', '']
        );
        $invalidUrls8 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'https://webshop.url/rejected', 'https://webshop.url/cancelled', 'invalid', '']
        );
        $invalidUrls9 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'https://webshop.url/rejected', 'https://webshop.url/cancelled', 'https://webshop.url/ipn', '']
        );
        $invalidUrls10 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'https://webshop.url/rejected', 'https://webshop.url/cancelled', 'https://webshop.url/ipn', 'invalid']
        );

        $validUrls1 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://webshop.url/accepted', 'https://webshop.url/rejected', 'https://webshop.url/cancelled', 'https://webshop.url/ipn', 'https://webshop.url/checkout']
        );

        return [
            [$invalidUrls1, InvalidUrlsException::class],
            [$invalidUrls2, InvalidUrlsException::class],
            [$invalidUrls3, InvalidUrlsException::class],
            [$invalidUrls4, InvalidUrlsException::class],
            [$invalidUrls5, InvalidUrlsException::class],
            [$invalidUrls6, InvalidUrlsException::class],
            [$invalidUrls7, InvalidUrlsException::class],
            [$invalidUrls8, InvalidUrlsException::class],
            [$invalidUrls9, InvalidUrlsException::class],
            [$invalidUrls10, InvalidUrlsException::class],
            [$validUrls1, null],
        ];
    }
}
