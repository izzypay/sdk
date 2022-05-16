<?php

declare(strict_types=1);

namespace IzzyPay\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Traits\HmacTrait;
use JsonException;

class RequestService
{
    use HmacTrait;

    private const REQUEST_TIMEOUT = 20;
    private const SDK_HEADER_FIELD = 'X-Sdk-Version';
    private const SDK_VERSION = '1.0';
    private const HMAC_ALGORITHM = 'sha256';

    public const CRED_ENDPOINT = '/api/opencart/cred';
    public const INIT_ENDPOINT = '/api/opencart/init';
    public const START_ENDPOINT = '/api/opencart/start';

    private string $merchantId;
    private string $baseUrl;
    private Client $client;

    public function __construct(string $merchantId, string $merchantSecret, string $baseUrl)
    {
        $this->merchantId = $merchantId;
        $this->merchantSecret = $merchantSecret;
        $this->hmacAlgorithm = self::HMAC_ALGORITHM;
        $this->baseUrl = $baseUrl;

        $this->client = new Client();
    }

    /**
     * @throws InvalidResponseException
     * @throws RequestException
     */
    public function sendHeadRequest(string $endpoint): void
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $authorizationHeader = $this->generateAuthorizationHeader($this->merchantId);
            $response = $this->client->head($url, [
                'timeout' => self::REQUEST_TIMEOUT,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationHeader,
                    self::SDK_HEADER_FIELD => self::SDK_VERSION,
                ]
            ]);
            $this->validateResponse($response);
        } catch (GuzzleException $exception) {
            throw new RequestException($exception->getMessage());
        }
    }

    /**
     * @param string $endpoint
     * @param array $body
     * @return string
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws RequestException
     */
    public function sendPostRequest(string $endpoint, array $body): string
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $requestBody = json_encode($body, JSON_THROW_ON_ERROR);
            $authorizationHeader = $this->generateAuthorizationHeader($this->merchantId, $requestBody);
            $response = $this->client->post($url, [
                'timeout' => self::REQUEST_TIMEOUT,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationHeader,
                    self::SDK_HEADER_FIELD => self::SDK_VERSION,
                ],
                'body' => $requestBody,
            ]);
            $this->validateResponse($response);
            return $response->getBody()->getContents();
        } catch (GuzzleException $exception) {
            throw new RequestException($exception->getMessage());
        }
    }

    /**
     * @param Response $response
     * @throws InvalidResponseException
     */
    private function validateResponse(Response $response): void
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
