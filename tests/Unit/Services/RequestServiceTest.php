<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidResponseException;
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
    private const BASE_URL = 'https://www.example.com';

    private Client|MockInterface $guzzleClientMock;
    private HmacService|MockObject $hmacServiceMock;
    private ResponseValidator|MockObject $responseValidatorMock;

    protected function setUp(): void
    {
        $this->hmacServiceMock = $this->getMockBuilder(HmacService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseValidatorMock = $this->getMockBuilder(ResponseValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->guzzleClientMock = Mockery::mock('overload:' . Client::class);
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
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
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
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
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
    public function testSendHeadRequest(): void
    {
        $endpoint = '/endpoint';
        $authorizationHeader = 'HMAC merchantId:signature';
        $options = [
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
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
     * @throws InvalidResponseException
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
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
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
            'timeout' => 20,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
                'X-Sdk-Version' => '1.0',
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
    public function testSendPostRequest(): void
    {

        $endpoint = '/endpoint';
        $body = [
            'key' => 'value',
        ];
        $authorizationHeader = 'HMAC merchantId:signature';
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
        $this->assertEqualsCanonicalizing($responseData, $response);
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
            $this->hmacServiceMock,
            $this->responseValidatorMock,
        );
    }
}