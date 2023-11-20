<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Services\HmacService;
use IzzyPay\Services\RequestService;
use IzzyPay\Tests\Helpers\Traits\InvokeMethodTrait;
use IzzyPay\Validators\ResponseValidator;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class RequestServiceTest extends TestCase
{
    use InvokeMethodTrait;

    private const MERCHANT_ID = 'merchantId';
    private const BASE_URL = 'https://test.izzypay.hu';
    private const PLUGIN_VERSION = 'plugin 1.0';
    private const REQUEST_TIMEOUT = 0.5;
    private const SDK_VERSION = '1.1.0';

    private MockInterface $guzzleClientMock;
    private MockObject $hmacServiceMock;
    private MockObject $responseValidatorMock;
    private string $pluginVersionHeaderWithShopPlugin;
    private string $pluginVersionHeaderWithoutShopPlugin;

    protected function setUp(): void
    {
        $this->hmacServiceMock = $this->getMockBuilder(HmacService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseValidatorMock = $this->getMockBuilder(ResponseValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->guzzleClientMock = Mockery::mock('overload:' . Client::class);
        $this->pluginVersionHeaderWithoutShopPlugin = '(SDK: ' . self::SDK_VERSION . ')';
        $this->pluginVersionHeaderWithShopPlugin = self::PLUGIN_VERSION . ' ' . $this->pluginVersionHeaderWithoutShopPlugin;
    }

    // <editor-fold desc=sendHeadRequest>

    /**
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function testSendHeadRequestWithRequestException(): void
    {
        $endpoint = '/endpoint';
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
        ];

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('head')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andThrow(new TransferException());
        $this->responseValidatorMock
            ->expects($this->exactly(0))
            ->method('validateResponseAuthentication');

        $this->expectException(RequestException::class);
        $requestService = $this->getNewRequestService();
        $requestService->sendHeadRequest($endpoint);
    }

    /**
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function testSendHeadRequestWithAuthenticationException(): void
    {
        $endpoint = '/endpoint';
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
        ];
        $response = new Response(200);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn('HMAC merchantId:signature');
        $this->guzzleClientMock
            ->shouldReceive('head')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($response);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($response)
            ->willThrowException(new AuthenticationException(''));

        $this->expectException(AuthenticationException::class);
        $requestService = $this->getNewRequestService();
        $requestService->sendHeadRequest($endpoint);
    }

    /**
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function testSendHeadRequestWithoutPlugin(): void
    {
        $endpoint = '/endpoint';
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithoutShopPlugin,
            ],
        ];
        $response = new Response(200);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn('HMAC merchantId:signature');
        $this->guzzleClientMock
            ->shouldReceive('head')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($response);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($response);

        $requestService = new RequestService(
            self::MERCHANT_ID,
            self::BASE_URL,
            null,
            $this->hmacServiceMock,
            $this->responseValidatorMock,
        );
        $requestService->sendHeadRequest($endpoint);
        $this->assertTrue(true);
    }

    /**
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function testSendHeadRequest(): void
    {
        $endpoint = '/endpoint';
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
        ];
        $response = new Response(200);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn('HMAC merchantId:signature');
        $this->guzzleClientMock
            ->shouldReceive('head')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($response);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($response);

        $requestService = $this->getNewRequestService();
        $requestService->sendHeadRequest($endpoint);
        $this->assertTrue(true);
    }
    // </editor-fold>

    // <editor-fold desc=sendPostRequest>
    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPostRequestWithRequestException(): void
    {
        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('post')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andThrow(new TransferException());
        $this->responseValidatorMock
            ->expects($this->exactly(0))
            ->method('validateResponseAuthentication');

        $this->expectException(RequestException::class);
        $requestService = $this->getNewRequestService();
        $requestService->sendPostRequest($endpoint, $body);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPostRequestWithAuthenticationException(): void
    {
        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];
        $response = new Response(200);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('post')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($response);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->willThrowException(new AuthenticationException(''));

        $this->expectException(AuthenticationException::class);
        $requestService = $this->getNewRequestService();
        $requestService->sendPostRequest($endpoint, $body);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPostRequestWithoutPlugin(): void
    {

        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithoutShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];

        $responseData = [
            'token' => 'token',
            'available' => true,
        ];
        $encodedResponseData = json_encode($responseData, JSON_THROW_ON_ERROR);
        $responseHeader = [
            'Authorization' => "HMAC signature",
        ];
        $mockResponse = new Response(200, $responseHeader, $encodedResponseData);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('post')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($mockResponse);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($mockResponse);

        $requestService = new RequestService(
            self::MERCHANT_ID,
            self::BASE_URL,
            null,
            $this->hmacServiceMock,
            $this->responseValidatorMock,
        );
        $response = $requestService->sendPostRequest($endpoint, $body);
        $this->assertEquals($responseData, $response);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPostRequest(): void
    {

        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];

        $responseData = [
            'token' => 'token',
            'available' => true,
        ];
        $encodedResponseData = json_encode($responseData, JSON_THROW_ON_ERROR);
        $responseHeader = [
            'Authorization' => "HMAC signature",
        ];
        $mockResponse = new Response(200, $responseHeader, $encodedResponseData);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('post')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($mockResponse);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($mockResponse);

        $requestService = $this->getNewRequestService();
        $response = $requestService->sendPostRequest($endpoint, $body);
        $this->assertEquals($responseData, $response);
    }
    // </editor-fold>

    // <editor-fold desc=sendPutRequest>
    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPutRequestWithRequestException(): void
    {
        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('put')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andThrow(new TransferException());
        $this->responseValidatorMock
            ->expects($this->exactly(0))
            ->method('validateResponseAuthentication');

        $this->expectException(RequestException::class);
        $requestService = $this->getNewRequestService();
        $requestService->sendPutRequest($endpoint, $body);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPutRequestWithAuthenticationException(): void
    {
        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];
        $response = new Response(200);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('put')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($response);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->willThrowException(new AuthenticationException(''));

        $this->expectException(AuthenticationException::class);
        $requestService = $this->getNewRequestService();
        $requestService->sendPutRequest($endpoint, $body);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPutRequestWithoutPlugin(): void
    {

        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithoutShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];

        $responseData = [
            'token' => 'token',
            'available' => true,
        ];
        $encodedResponseData = json_encode($responseData, JSON_THROW_ON_ERROR);
        $responseHeader = [
            'Authorization' => "HMAC signature",
        ];
        $mockResponse = new Response(200, $responseHeader, $encodedResponseData);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('put')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($mockResponse);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($mockResponse);

        $requestService = new RequestService(
            self::MERCHANT_ID,
            self::BASE_URL,
            null,
            $this->hmacServiceMock,
            $this->responseValidatorMock,
        );
        $response = $requestService->sendPutRequest($endpoint, $body);
        $this->assertEquals($responseData, $response);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPutRequestWithoutData(): void
    {

        $endpoint = '/endpoint';
        $body = [];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];

        $responseData = [];
        $encodedResponseData = json_encode($responseData, JSON_THROW_ON_ERROR);
        $responseHeader = [
            'Authorization' => "HMAC signature",
        ];
        $mockResponse = new Response(200, $responseHeader, $encodedResponseData);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('put')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($mockResponse);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($mockResponse);

        $requestService = $this->getNewRequestService();
        $response = $requestService->sendPutRequest($endpoint);
        $this->assertEquals($responseData, $response);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testSendPutRequestWithData(): void
    {

        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Plugin-Version' => $this->pluginVersionHeaderWithShopPlugin,
            ],
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ];

        $responseData = [
            'returnDate' => '2022-04-04T12:34:56+0010',
            'reducedValue' => 100.2,
        ];
        $encodedResponseData = json_encode($responseData, JSON_THROW_ON_ERROR);
        $responseHeader = [
            'Authorization' => "HMAC signature",
        ];
        $mockResponse = new Response(200, $responseHeader, $encodedResponseData);

        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with(self::MERCHANT_ID)
            ->willReturn($authorizationHeader);
        $this->guzzleClientMock
            ->shouldReceive('put')
            ->once()
            ->with(self::BASE_URL . $endpoint, $options)
            ->andReturn($mockResponse);
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateResponseAuthentication')
            ->with($mockResponse);

        $requestService = $this->getNewRequestService();
        $response = $requestService->sendPutRequest($endpoint, $body);
        $this->assertEquals($responseData, $response);
    }
    // </editor-fold>

    // <editor-fold desc=validateAuthentication>
    public function testValidateAuthenticationWithInvalidAuthorizationHeader(): void
    {
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateAuthentication')
            ->with('content', 'Bearer signature')
            ->willThrowException(new AuthenticationException('Invalid signature'));

        $this->expectException(AuthenticationException::class);
        $requestService = $this->getNewRequestService();
        $requestService->validateAuthentication('content', 'Bearer signature');
    }

    /**
     * @throws AuthenticationException
     */
    public function validateAuthentication(): void
    {
        $this->responseValidatorMock
            ->expects($this->once())
            ->method('validateAuthentication')
            ->with('content', 'Bearer signature');

        $requestService = $this->getNewRequestService();
        $requestService->validateAuthentication('content', 'Bearer merchant:signature');

        $this->assertTrue(true);
    }
    // </editor-fold>

    // <editor-fold desc=generateAuthorizationHeader>
    public function testGenerateAuthorizationHeader(): void
    {
        $this->hmacServiceMock
            ->expects($this->once())
            ->method('generateAuthorizationHeader')
            ->with('merchantId', 'content')
            ->willReturn('HMAC merchantId:signature');

        $requestService = $this->getNewRequestService();
        $actual = $requestService->generateAuthorizationHeader('content');

        $this->assertEquals('HMAC merchantId:signature', $actual);
    }
    // </editor-fold>

    public function tearDown(): void
    {
        Mockery::close();
    }

    private function getNewRequestService(): RequestService
    {
        return new RequestService(
            self::MERCHANT_ID,
            self::BASE_URL,
            self::PLUGIN_VERSION,
            $this->hmacServiceMock,
            $this->responseValidatorMock,
        );
    }
}
