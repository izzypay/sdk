<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Services\RequestService;
use IzzyPay\Tests\Helpers\Traits\InvokeMethodTrait;
use IzzyPay\Traits\HmacTrait;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * @runTestsInSeparateProcesses
 */
class RequestServiceTest extends TestCase
{
    use HmacTrait;
    use InvokeMethodTrait;

    private const MERCHANT_ID = 'merchantId';
    private const MERCHANT_SECRET = 'merchantSecret';
    private const BASE_URL = 'https://www.example.com';
    private const HMAC_ALGORITHM = 'sha384';

    private Client|MockInterface $guzzleClientMock;

    protected function setUp(): void
    {
        $this->merchantSecret = self::MERCHANT_SECRET;
        $this->hmacAlgorithm = self::HMAC_ALGORITHM;
        $this->guzzleClientMock = Mockery::mock('overload:' . Client::class);
    }

    // <editor-fold desc=sendHeadRequest>
    /**
     * @throws InvalidResponseException
     * @throws RequestException
     */
    public function testSendHeadRequestWithRequestException(): void
    {
        $endpoint = '/endpoint';
        $authorizationHeader = $this->generateAuthorizationHeader(self::MERCHANT_ID);
        $options = [
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
            ],
        ];
        $this->guzzleClientMock
            ->shouldReceive('head')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andThrow(new TransferException());
        $this->expectException(RequestException::class);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $requestService->sendHeadRequest($endpoint);
    }

    /**
     * @throws InvalidResponseException
     * @throws RequestException
     */
    public function testSendHeadRequestWithInvalidResponseException(): void
    {
        $endpoint = '/endpoint';
        $this->guzzleClientMock
            ->shouldReceive('head')
            ->once()
            ->andReturn(new Response(200));
        $this->expectException(InvalidResponseException::class);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $requestService->sendHeadRequest($endpoint);
    }

    /**
     * @throws InvalidResponseException
     * @throws RequestException
     */
    public function testSendHeadRequest(): void
    {
        $endpoint = '/endpoint';
        $authorizationHeader = $this->generateAuthorizationHeader(self::MERCHANT_ID);
        $options = [
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0'
            ]
        ];
        $signature = $this->generateSignature('');
        $responseHeader = [
            'Authorization' => "HMAC $signature",
        ];
        $mockResponse = new Response(200, $responseHeader, '');
        $this->guzzleClientMock
            ->shouldReceive('head')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($mockResponse);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $requestService->sendHeadRequest($endpoint);
        $this->assertTrue(true);
    }
    // </editor-fold>

    // <editor-fold desc=sendPostRequest>
    /**
     * @throws InvalidResponseException
     * @throws RequestException
     * @throws JsonException
     */
    public function testSendPostRequestWithRequestException(): void
    {
        $endpoint = '/endpoint';
        $body = [
            'token' => 'token',
            'available' => true,
        ];
        $encodedBody = json_encode($body, JSON_THROW_ON_ERROR);
        $authorizationHeader = $this->generateAuthorizationHeader(self::MERCHANT_ID, $encodedBody);
        $options = [
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
            ],
            'body' => $encodedBody,
        ];
        $this->guzzleClientMock
            ->shouldReceive('post')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andThrow(new TransferException());
        $this->expectException(RequestException::class);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $requestService->sendPostRequest($endpoint, $body);
    }

    /**
     * @throws InvalidResponseException
     * @throws RequestException
     * @throws JsonException
     */
    public function testSendPostRequestWithInvalidResponseException(): void
    {
        $endpoint = '/endpoint';
        $this->guzzleClientMock
            ->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200));
        $this->expectException(InvalidResponseException::class);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $requestService->sendPostRequest($endpoint, []);
    }

    /**
     * @throws InvalidResponseException
     * @throws RequestException
     * @throws JsonException
     */
    public function testSendPostRequest(): void
    {
        $endpoint = '/endpoint';
        $body = [
            'token' => 'token',
            'available' => true,
        ];
        $authorizationHeader = $this->generateAuthorizationHeader(self::MERCHANT_ID, json_encode($body, JSON_THROW_ON_ERROR));
        $options = [
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];
        $responseData = [
            'token' => 'token',
            'available' => true,
        ];
        $encodedResponseData = json_encode($responseData, JSON_THROW_ON_ERROR);
        $signature = $this->generateSignature($encodedResponseData);
        $responseHeader = [
            'Authorization' => "HMAC $signature",
        ];
        $mockResponse = new Response(200, $responseHeader, $encodedResponseData);
        $this->guzzleClientMock
            ->shouldReceive('post')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($mockResponse);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $response = $requestService->sendPostRequest($endpoint, $body);
        $this->assertEqualsCanonicalizing($responseData, $response);
    }
    // </editor-fold>

    // <editor-fold desc=validateResponse>
    /**
     * @throws ReflectionException
     */
    public function testValidateResponseWithoutAuthorizationHeader(): void
    {
        $mockResponse = new Response(200);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $this->expectException(InvalidResponseException::class);
        $this->invokeMethod($requestService, 'validateResponse', [$mockResponse]);
    }

    /**
     * @throws ReflectionException
     */
    public function testValidateResponseWithInvalidAuthorizationHeader(): void
    {
        $responseHeader = [
            'Authorization' => 'Bearer signature',
        ];
        $mockResponse = new Response(200, $responseHeader);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $this->expectException(InvalidResponseException::class);
        $this->invokeMethod($requestService, 'validateResponse', [$mockResponse]);
    }

    /**
     * @throws ReflectionException
     */
    public function testValidateResponseWithInvalidSignature(): void
    {
        $responseHeader = [
            'Authorization' => 'HMAC signature',
        ];
        $mockResponse = new Response(200, $responseHeader);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $this->expectException(InvalidResponseException::class);
        $this->invokeMethod($requestService, 'validateResponse', [$mockResponse]);
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function testValidateResponse(): void
    {
        $responseData = [
            'token' => 'token',
            'available' => true,
        ];
        $encodedResponseData = json_encode($responseData, JSON_THROW_ON_ERROR);
        $signature = $this->generateSignature($encodedResponseData);
        $responseHeader = [
            'Authorization' => "HMAC $signature",
        ];
        $mockResponse = new Response(200, $responseHeader, $encodedResponseData);
        $requestService = new RequestService(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $this->invokeMethod($requestService, 'validateResponse', [$mockResponse]);
        $this->assertTrue(true);
    }
    // </editor-fold>

    public function tearDown(): void
    {
        Mockery::close();
    }
}
