<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\PaymentServiceUnavailableException;
use IzzyPay\Services\HmacService;
use Psr\Http\Message\ResponseInterface;

class ResponseValidator
{
    private HmacService $hmacService;

    /**
     * @param HmacService $hmacService
     */
    public function __construct(HmacService $hmacService)
    {
        $this->hmacService = $hmacService;
    }

    /**
     * @param ResponseInterface $response
     * @return void
     * @throws AuthenticationException
     */
    public function validateResponseAuthentication(ResponseInterface $response): void
    {
        if (!$response->hasHeader('Authorization')) {
            throw new AuthenticationException('Missing authorization header');
        }

        $authorizationHeader = $response->getHeader('Authorization')[0];
        $signature = $this->hmacService->getSignature($authorizationHeader);
        if ($signature === null) {
            throw new AuthenticationException('Invalid authorization header');
        }

        $content = $response->getBody()->getContents();
        $response->getBody()->rewind();
        $calculatedEncodedSignature = $this->hmacService->generateSignature($content);
        if ($calculatedEncodedSignature !== $signature) {
            throw new AuthenticationException('Invalid signature');
        }
    }

    /**
     * @param array $response
     * @return void
     * @throws InvalidResponseException
     */
    public function validateInitResponse(array $response): void
    {
        $errors = $this->validate($response);

        if (!array_key_exists('jsUrl', $response) || !filter_var($response['jsUrl'], FILTER_VALIDATE_URL)) {
            $errors[] = 'jsUrl';
        }

        if (count($errors) > 0) {
            throw new InvalidResponseException($errors);
        }
    }

    /**
     * @param array $response
     * @return void
     * @throws InvalidResponseException
     */
    public function validateStartResponse(array $response): void
    {
        $errors = $this->validate($response);
        if (count($errors) > 0) {
            throw new InvalidResponseException($errors);
        }
    }

    /**
     * @param array $response
     * @return void
     * @throws PaymentServiceUnavailableException
     */
    public function verifyInitAvailability(array $response): void
    {
        $errors = [];
        if (array_key_exists('errors', $response)) {
            $errors = $response['errors'];
        }
        if (!array_key_exists('available', $response) || ($response['available'] !== true) || (count($errors) > 0 )) {
            throw new PaymentServiceUnavailableException($errors);
        }
    }

    /**
     * @param array $response
     * @return void
     * @throws PaymentServiceUnavailableException
     */
    public function verifyStartAvailability(array $response): void
    {
        if (array_key_exists('errors', $response) && (count($response['errors']) > 0)) {
            throw new PaymentServiceUnavailableException($response['errors']);
        }
    }

    /**
     * @param array $response
     * @return array
     */
    private function validate(array $response): array
    {
        $errors = [];

        if (!array_key_exists('token', $response) || ($response['token'] === null) || (trim($response['token']) === '')) {
            $errors[] = 'token';
        }

        if (!array_key_exists('merchantId', $response) || ($response['merchantId'] === null) || (trim($response['merchantId']) === '')) {
            $errors[] = 'merchantId';
        }

        if (!array_key_exists('merchantCartId', $response) || ($response['merchantCartId'] === null) || (trim($response['merchantCartId']) === '')) {
            $errors[] = 'merchantCartId';
        }

        return $errors;
    }
}
