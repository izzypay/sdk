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
            ['https://accepted.com', '', '', '', '']
        );
        $invalidUrls4 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'invalid', '', '', '']
        );
        $invalidUrls5 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'https://rejected.com', '', '', '']
        );
        $invalidUrls6 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'https://rejected.com', 'invalid', '', '']
        );
        $invalidUrls7 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'https://rejected.com', 'https://cancelled.com', '', '']
        );
        $invalidUrls8 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'https://rejected.com', 'https://cancelled.com', 'invalid', '']
        );
        $invalidUrls9 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'https://rejected.com', 'https://cancelled.com', 'https://ipn.com', '']
        );
        $invalidUrls10 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'https://rejected.com', 'https://cancelled.com', 'https://ipn.com', 'invalid']
        );

        $validUrls1 = $this->invokeConstructor(
            RedirectUrls::class,
            ['https://accepted.com', 'https://rejected.com', 'https://cancelled.com', 'https://ipn.com', 'https://checkout.com']
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
