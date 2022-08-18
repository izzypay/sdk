<?php

declare(strict_types=1);

namespace IzzyPay\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Validators\ResponseValidator;
use JsonException;

class RequestService
{
    private const REQUEST_TIMEOUT = 20;
    private const SDK_HEADER_FIELD = 'X-Plugin-Version';
    private const SDK_VERSION = '1.0.4';

    private string $merchantId;
    private string $baseUrl;
    private string $pluginVersionHeader;
    private HmacService $hmacService;
    private ResponseValidator $responseValidator;
    private Client $client;

    /**
     * @param string $merchantId
     * @param string $baseUrl
     * @param string|null $pluginVersion
     * @param HmacService $hmacService
     * @param ResponseValidator $responseValidator
     */
    public function __construct(string $merchantId, string $baseUrl, ?string $pluginVersion, HmacService $hmacService, ResponseValidator $responseValidator)
    {
        $this->merchantId = $merchantId;
        $this->baseUrl = $baseUrl;
        $this->hmacService = $hmacService;
        $this->responseValidator = $responseValidator;


        $this->pluginVersionHeader = trim($pluginVersion . ' (SDK: ' . self::SDK_VERSION . ')');
        $this->client = new Client();
    }

    /**
     * @throws AuthenticationException
     * @throws RequestException
     */
    public function sendHeadRequest(string $endpoint): void
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $authorizationHeader = $this->hmacService->generateAuthorizationHeader($this->merchantId);
            $response = $this->client->head($url, [
                'timeout' => self::REQUEST_TIMEOUT,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationHeader,
                    self::SDK_HEADER_FIELD => $this->pluginVersionHeader,
                ],
            ]);
            $this->responseValidator->validateResponseAuthentication($response);
        } catch (GuzzleException $exception) {
            throw new RequestException($exception->getMessage());
        }
    }

    /**
     * @param string $endpoint
     * @param array $body
     * @return array
     * @throws JsonException
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function sendPostRequest(string $endpoint, array $body): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $requestBody = json_encode($body, JSON_THROW_ON_ERROR);
            $authorizationHeader = $this->hmacService->generateAuthorizationHeader($this->merchantId, $requestBody);
            $response = $this->client->post($url, [
                'timeout' => self::REQUEST_TIMEOUT,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationHeader,
                    self::SDK_HEADER_FIELD => $this->pluginVersionHeader,
                ],
                'body' => $requestBody,
            ]);
            $this->responseValidator->validateResponseAuthentication($response);
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $exception) {
            throw new RequestException($exception->getMessage());
        }
    }
}
