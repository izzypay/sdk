<?php

declare(strict_types=1);

namespace IzzyPay;

use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Exceptions\PaymentServiceUnavailableException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Cart;
use IzzyPay\Models\Customer;
use IzzyPay\Models\Other;
use IzzyPay\Models\Response\InitResponse;
use IzzyPay\Models\Response\StartResponse;
use IzzyPay\Models\Urls;
use IzzyPay\Services\HmacService;
use IzzyPay\Services\RequestService;
use IzzyPay\Validators\CartValidator;
use IzzyPay\Validators\CustomerValidator;
use IzzyPay\Validators\OtherValidator;
use IzzyPay\Validators\ResponseValidator;
use IzzyPay\Validators\UrlsValidator;
use JsonException;

class IzzyPay
{
    // TODO: Update with the final endpoints
    public const CRED_ENDPOINT = '/api/opencart/cred';
    public const INIT_ENDPOINT = '/api/opencart/init';
    public const START_ENDPOINT = '/api/opencart/start';

    private string $merchantId;
    private ResponseValidator $responseValidator;
    private RequestService $requestService;

    public function __construct(string $merchantId, string $merchantSecret, string $baseUrl)
    {
        $this->merchantId = $merchantId;

        $hmacService = new HmacService($merchantSecret);
        $this->responseValidator = new ResponseValidator($hmacService);
        $this->requestService = new RequestService($merchantId, $baseUrl, $hmacService, $this->responseValidator);
    }

    /**
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function cred(): void
    {
        $this->requestService->sendHeadRequest(self::CRED_ENDPOINT);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param LimitedCustomer $limitedCustomer
     * @param Other $other
     * @return InitResponse
     * @throws AuthenticationException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws PaymentServiceUnavailableException
     * @throws RequestException
     * @throws InvalidCartItemException
     * @throws InvalidCartException
     * @throws InvalidCustomerException
     * @throws InvalidOtherException
     */
    public function init(string $merchantCartId, Cart $cart, LimitedCustomer $limitedCustomer, Other $other): InitResponse
    {
        $cartValidator = new CartValidator();
        $cartValidator->validateCart($cart);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateLimitedCustomer($limitedCustomer);

        $otherValidator = new OtherValidator();
        $otherValidator->validateOther($other);

        $body = $this->prepareInitRequestData($merchantCartId, $cart, $limitedCustomer, $other);
        $response = $this->requestService->sendPostRequest(self::INIT_ENDPOINT, $body);
        $this->responseValidator->validateInitResponse($response);
        $this->responseValidator->verifyInitAvailability($response);
        return new InitResponse($response['token'], $response['merchantId'], $response['merchantCartId'], $response['jsUrl']);
    }

    /**
     * @param string $token
     * @param string $merchantCartId
     * @param Cart $cart
     * @param Customer $customer
     * @param Other $other
     * @param Urls $urls
     * @return StartResponse
     * @throws AuthenticationException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws RequestException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidCartItemException
     * @throws InvalidCartException
     * @throws InvalidAddressException
     * @throws InvalidCustomerException
     * @throws InvalidOtherException
     * @throws InvalidUrlsException
     */
    public function start(string $token, string $merchantCartId, Cart $cart, Customer $customer, Other $other, Urls $urls): StartResponse
    {
        $cartValidator = new CartValidator();
        $cartValidator->validateCart($cart);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateCustomer($customer);

        $otherValidator = new OtherValidator();
        $otherValidator->validateOther($other);

        $urlsValidator = new UrlsValidator();
        $urlsValidator->validateUrls($urls);

        $endpoint = self::START_ENDPOINT . '/' . $token;
        $body = $this->prepareStartRequestData($merchantCartId, $cart, $customer, $other, $urls);
        var_dump($body);
        $response = $this->requestService->sendPostRequest($endpoint, $body);
        $this->responseValidator->validateStartResponse($response);
        $this->responseValidator->verifyStartAvailability($response);
        return new StartResponse($response['token'], $response['merchantId'], $response['merchantCartId']);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param AbstractCustomer $customer
     * @param Other $other
     * @return array
     */
    private function prepareInitRequestData(string $merchantCartId, Cart $cart, AbstractCustomer $customer, Other $other): array
    {
        return [
            'merchantId' => $this->merchantId,
            'merchantCartId' => $merchantCartId,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
        ];
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param AbstractCustomer $customer
     * @param Other $other
     * @param Urls $urls
     * @return array
     */
    private function prepareStartRequestData(string $merchantCartId, Cart $cart, AbstractCustomer $customer, Other $other, Urls $urls): array
    {
        $data = $this->prepareInitRequestData($merchantCartId, $cart, $customer, $other);
        $data['urls'] = $urls->toArray();
        return $data;
    }
}