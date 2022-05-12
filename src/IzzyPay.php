<?php

declare(strict_types=1);

namespace Bnpl;

use Bnpl\Exception\InvalidResponseException;
use Bnpl\Traits\HmacTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class IzzyPay
{
    use HmacTrait;

    private const SDK_VERSION = '1.0';
    private const REQUEST_TIMEOUT = 20;
    private const CRED_ENDPOINT = '/api/opencart/cred';
    private const INIT_ENDPOINT = '/api/opencart/init';

    private string $merchantId;
    private string $baseUrl;
    private Client $client;

    public function __construct(string $merchantId, string $merchantSecret, string $baseUrl)
    {
        $this->merchantId = $merchantId;
        $this->merchantSecret = $merchantSecret;

        $this->client = new Client();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws InvalidResponseException
     * @throws \JsonException
     */
    public function cred(): void
    {
        $url = $this->baseUrl . self::CRED_ENDPOINT;
        $this->sendHeadRequest($url);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws InvalidResponseException
     * @throws \JsonException
     */
    public function init($merchantCartId, $cart, $customer, $other)
    {
        $url = $this->baseUrl . self::INIT_ENDPOINT;
        $this->sendPostRequest($url, []);
    }

    public function init2()
    {
    }

    public function start()
    {
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws InvalidResponseException
     */
    private function sendHeadRequest(string $url): void
    {
        $authorizationHeader = $this->generateAuthorizationHeader($this->merchantId);
        $response = $this->client->head($url, [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
            ]
        ]);
        $this->validateResponse($response);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws InvalidResponseException
     */
    private function sendPostRequest(string $url, array $body)
    {
        $requestBody = json_encode($body, JSON_THROW_ON_ERROR);
        $authorizationHeader = $this->generateAuthorizationHeader($this->merchantId, $requestBody);
        $response = $this->client->post($url, [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationHeader,
            ],
            'body' => $requestBody,
        ]);
        $this->validateResponse($response);
    }

    /**
     * @param Response $response
     * @throws InvalidResponseException
     */
    public function validateResponse(Response $response): void
    {
        if (!$response->hasHeader('Authorization')) {
            throw new InvalidResponseException('Missing authorization header');
        }

        $authorizationHeader = $response->getHeader('Authorization')[0];
        $signature = $this->getSignature($authorizationHeader);
        if ($signature === null) {
            throw new InvalidResponseException('Invalid authorization header');
        }

        $calculatedEncodedSignature = $this->generateSignature($response->getBody()->getContents());
        if ($calculatedEncodedSignature !== $signature) {
            throw new InvalidResponseException('Invalid signature');
        }
    }
}
