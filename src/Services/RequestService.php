<?php

declare(strict_types=1);

namespace IzzyPay\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Traits\HmacTrait;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class RequestService
{
    use HmacTrait;

    private const REQUEST_TIMEOUT = 20;
    private const SDK_HEADER_FIELD = 'X-Sdk-Version';
    private const SDK_VERSION = '1.0';
    private const HMAC_ALGORITHM = 'sha384';

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
     * @return array
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws RequestException
     */
    public function sendPostRequest(string $endpoint, array $body): array
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
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $exception) {
            throw new RequestException($exception->getMessage());
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws InvalidResponseException
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if (!$response->hasHeader('Authorization')) {
            throw new InvalidResponseException('Missing authorization header');
        }

        $authorizationHeader = $response->getHeader('Authorization')[0];
        $signature = $this->getSignature($authorizationHeader);
        if ($signature === null) {
            throw new InvalidResponseException('Invalid authorization header');
        }

        $content = $response->getBody()->getContents();
        $response->getBody()->rewind();
        $calculatedEncodedSignature = $this->generateSignature($content);
        if ($calculatedEncodedSignature !== $signature) {
            throw new InvalidResponseException('Invalid signature');
        }
    }
}
