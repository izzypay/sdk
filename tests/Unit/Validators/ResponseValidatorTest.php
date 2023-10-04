<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use GuzzleHttp\Psr7\Response;
use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\PaymentServiceUnavailableException;
use IzzyPay\Services\HmacService;
use IzzyPay\Tests\Helpers\Traits\InvokeMethodTrait;
use IzzyPay\Validators\ResponseValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ResponseValidatorTest extends TestCase
{
    use InvokeMethodTrait;

    private HmacService|MockObject $hmacServiceMock;

    protected function setUp(): void
    {
        $this->hmacServiceMock = $this->getMockBuilder(HmacService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @throws AuthenticationException
     */
    public function testValidateResponseAuthenticationWithoutAuthorizationHeader(): void
    {
        $responseValidator = $this->getNewResponseValidator();
        $response = new Response(200);
        $this->expectException(AuthenticationException::class);
        $responseValidator->validateResponseAuthentication($response);
    }

    /**
     * @throws AuthenticationException
     */
    public function testValidateResponseAuthenticationWithInvalidAuthorizationHeader(): void
    {
        $responseValidator = $this->getNewResponseValidator();
        $response = new Response(200, ['Authorization' => 'Bearer signature']);
        $this->hmacServiceMock->expects($this->once())->method('getSignature')->willReturn(null);
        $this->expectException(AuthenticationException::class);
        $responseValidator->validateResponseAuthentication($response);
    }

    /**
     * @throws AuthenticationException
     */
    public function testValidateResponseAuthenticationWithInvalidSignature(): void
    {
        $responseValidator = $this->getNewResponseValidator();
        $response = new Response(200, ['Authorization' => 'HMAC invalid']);
        $this->hmacServiceMock->expects($this->once())->method('getSignature')->willReturn('invalid');
        $this->hmacServiceMock->expects($this->once())->method('generateSignature')->willReturn('signature');
        $this->expectException(AuthenticationException::class);
        $responseValidator->validateResponseAuthentication($response);
    }

    /**
     * @throws AuthenticationException
     */
    public function testValidateResponseAuthentication(): void
    {
        $responseValidator = $this->getNewResponseValidator();
        $response = new Response(200, ['Authorization' => 'HMAC signature']);
        $this->hmacServiceMock->expects($this->once())->method('getSignature')->willReturn('signature');
        $this->hmacServiceMock->expects($this->once())->method('generateSignature')->willReturn('signature');
        $responseValidator->validateResponseAuthentication($response);
        $this->assertTrue(true);
    }

    /**
     * @dataProvider getResponseProvider
     * @throws ReflectionException
     */
    public function testValidate(array $response, array $expected): void
    {
        $responseValidator = $this->getNewResponseValidator();
        $errors = $this->invokeMethod($responseValidator, 'validate', [$response]);
        $this->assertEquals($expected, $errors);
    }

    /**
     * @dataProvider getInitResponseForValidationProvider
     * @throws InvalidResponseException
     */
    public function testValidateInitResponse(array $response, ?string $expectedExceptionClass): void
    {
        $responseValidator = $this->getNewResponseValidator();
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }
        $responseValidator->validateInitResponse($response);
        if (!$expectedExceptionClass) {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getRedirectInitResponseForValidationProvider
     * @throws InvalidResponseException
     */
    public function testValidateRedirectInitResponse(array $response, ?string $expectedExceptionClass): void
    {
        $responseValidator = $this->getNewResponseValidator();
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }
        $responseValidator->validateInitResponse($response);
        if (!$expectedExceptionClass) {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getStartResponseForValidationProvider
     * @throws InvalidResponseException
     */
    public function testValidateStartResponse(array $response, ?string $expectedExceptionClass): void
    {
        $responseValidator = $this->getNewResponseValidator();
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }
        $responseValidator->validateStartResponse($response);
        if (!$expectedExceptionClass) {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getInitResponseForAvailabilityProvider
     * @throws PaymentServiceUnavailableException
     */
    public function testVerifyInitResponseAvailability(array $response, ?string $expectedExceptionClass): void
    {
        $responseValidator = $this->getNewResponseValidator();
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }
        $responseValidator->verifyInitAvailability($response);
        if (!$expectedExceptionClass) {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getStartResponseForAvailabilityProvider
     * @throws PaymentServiceUnavailableException
     */
    public function testVerifyStartResponseAvailability(array $response, ?string $expectedExceptionClass): void
    {
        $responseValidator = $this->getNewResponseValidator();
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }
        $responseValidator->verifyStartAvailability($response);
        if (!$expectedExceptionClass) {
            $this->assertTrue(true);
        }
    }

    public function getResponseProvider(): array
    {
        return [
            [
                [],
                ['token', 'merchantId', 'merchantCartId'],
            ],
            [
                [
                    'token' => 'token',
                    'merchantId' => 'merchant id',
                    'merchantCartId' => 'merchant cart id',
                ],
                []
            ],
        ];
    }

    public function getInitResponseForValidationProvider(): array
    {
        return [
            [
                [],
                InvalidResponseException::class,
            ],
            [
                [
                    'token' => 'token',
                    'merchantId' => 'merchant id',
                    'merchantCartId' => 'merchant cart id',
                ],
                InvalidResponseException::class,
            ],
            [
                [
                    'token' => 'token',
                    'merchantId' => 'merchant id',
                    'merchantCartId' => 'merchant cart id',
                    'jsUrl' => 'js url'
                ],
                InvalidResponseException::class,
            ],
            [
                [
                    'token' => 'token',
                    'merchantId' => 'merchant id',
                    'merchantCartId' => 'merchant cart id',
                    'jsUrl' => 'https://www.example.com'
                ],
                null
            ],
        ];
    }

    public function getRedirectInitResponseForValidationProvider(): array
    {
        return [
            [
                [],
                InvalidResponseException::class,
            ],
            [
                [
                    'token' => 'token',
                    'merchantId' => 'merchant id',
                    'merchantCartId' => 'merchant cart id',
                    'jsUrl' => 'https://www.example.com'
                ],
                null,
            ],
        ];
    }

    public function getStartResponseForValidationProvider(): array
    {
        return [
            [
                [],
                InvalidResponseException::class,
            ],
            [
                [
                    'token' => 'token',
                    'merchantId' => 'merchant id',
                    'merchantCartId' => 'merchant cart id',
                ],
                null,
            ],
        ];
    }

    public function getInitResponseForAvailabilityProvider(): array
    {
        return [
            [
                [
                    'available1' => true,
                ],
                PaymentServiceUnavailableException::class,
            ],
            [
                [
                    'available' => false,
                ],
                PaymentServiceUnavailableException::class,
            ],
            [
                [
                    'available' => true,
                    'errors' => [
                        'merchantId' => 'required',
                    ]
                ],
                PaymentServiceUnavailableException::class,
            ],
            [
                [
                    'available' => true,
                ],
                null,
            ],
        ];
    }

    public function getStartResponseForAvailabilityProvider(): array
    {
        return [
            [
                [
                    'errors' => [
                        'key' => 'value'
                    ],
                ],
                PaymentServiceUnavailableException::class,
            ],
            [
                [
                ],
                null,
            ],
        ];
    }

    private function getNewResponseValidator(): ResponseValidator
    {
        return new ResponseValidator($this->hmacServiceMock);
    }
}
