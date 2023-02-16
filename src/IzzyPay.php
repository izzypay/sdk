<?php

declare(strict_types=1);

namespace IzzyPay;

use DateTimeImmutable;
use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\InvalidReturnDataException;
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
use IzzyPay\Models\StartOther;
use IzzyPay\Models\Urls;
use IzzyPay\Services\HmacService;
use IzzyPay\Services\RequestService;
use IzzyPay\Validators\CartValidator;
use IzzyPay\Validators\CustomerValidator;
use IzzyPay\Validators\OtherValidator;
use IzzyPay\Validators\ResponseValidator;
use IzzyPay\Validators\ReturnValidator;
use IzzyPay\Validators\UrlsValidator;
use JsonException;

class IzzyPay
{
    // TODO: Update with the final endpoints
    public const CRED_ENDPOINT = '/api/v1/cred';
    public const INIT_ENDPOINT = '/api/v1/init';
    public const START_ENDPOINT = '/api/v1/start';
    public const DELIVERY_ENDPOINT = '/api/v1/delivery';
    public const RETURN_ENDPOINT = '/api/v1/return';

    private string $merchantId;
    private ResponseValidator $responseValidator;
    private RequestService $requestService;

    public function __construct(string $merchantId, string $merchantSecret, string $baseUrl, ?string $pluginVersion = null)
    {
        $this->merchantId = $merchantId;

        $hmacService = new HmacService($merchantSecret);
        $this->responseValidator = new ResponseValidator($hmacService);
        $this->requestService = new RequestService($merchantId, $baseUrl, $pluginVersion, $hmacService, $this->responseValidator);
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
    public function start(string $token, string $merchantCartId, Cart $cart, Customer $customer, StartOther $other, Urls $urls): StartResponse
    {
        $cartValidator = new CartValidator();
        $cartValidator->validateCart($cart);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateCustomer($customer);

        $otherValidator = new OtherValidator();
        $otherValidator->validateStartOther($other);

        $urlsValidator = new UrlsValidator();
        $urlsValidator->validateUrls($urls);

        $endpoint = self::START_ENDPOINT . '/' . $token;
        $body = $this->prepareStartRequestData($merchantCartId, $cart, $customer, $other, $urls);
        $response = $this->requestService->sendPostRequest($endpoint, $body);
        $this->responseValidator->validateStartResponse($response);
        $this->responseValidator->verifyStartAvailability($response);
        return new StartResponse($response['token'], $response['merchantId'], $response['merchantCartId']);
    }

    /**
     * @param string $merchantCartId
     * @throws AuthenticationException
     * @throws JsonException
     * @throws RequestException
     */
    public function deliveryCart(string $merchantCartId): void
    {
        $endpoint = self::DELIVERY_ENDPOINT . "/$this->merchantId/$merchantCartId";
        $this->requestService->sendPutRequest($endpoint);
    }

    /**
     * @param string $merchantCartId
     * @param string $merchantItemId
     * @throws AuthenticationException
     * @throws JsonException
     * @throws RequestException
     */
    public function deliveryItem(string $merchantCartId, string $merchantItemId): void
    {
        $endpoint = self::DELIVERY_ENDPOINT . "/$this->merchantId/$merchantCartId/$merchantItemId";
        $this->requestService->sendPutRequest($endpoint);
    }

    /**
     * @param string $merchantCartId
     * @param DateTimeImmutable $returnDate
     * @throws AuthenticationException
     * @throws JsonException
     * @throws RequestException
     */
    public function returnCart(string $merchantCartId, DateTimeImmutable $returnDate): void
    {
        $endpoint = self::RETURN_ENDPOINT . "/$this->merchantId/$merchantCartId";
        $data = [
            'returnDate' => $returnDate->format(DateTimeImmutable::ISO8601),
        ];
        $this->requestService->sendPutRequest($endpoint, $data);
    }

    /**
     * @param string $merchantCartId
     * @param string $merchantItemId
     * @param DateTimeImmutable $returnDate
     * @param float|null $reducedValue
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     * @throws JsonException
     * @throws RequestException
     */
    public function returnItem(string $merchantCartId, string $merchantItemId, DateTimeImmutable $returnDate, ?float $reducedValue = null): void
    {
        $returnValidator = new ReturnValidator();
        $returnValidator->validate($reducedValue);

        $endpoint = self::RETURN_ENDPOINT . "/$this->merchantId/$merchantCartId/$merchantItemId";
        $data = [
            'returnDate' => $returnDate->format(DateTimeImmutable::ISO8601),
        ];
        if ($reducedValue) {
            $data['reducedValue'] = $reducedValue;
        }
        $this->requestService->sendPutRequest($endpoint, $data);
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
     * @param StartOther $other
     * @param Urls $urls
     * @return array
     */
    private function prepareStartRequestData(string $merchantCartId, Cart $cart, AbstractCustomer $customer, StartOther $other, Urls $urls): array
    {
        return [
            'merchantId' => $this->merchantId,
            'merchantCartId' => $merchantCartId,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];
    }
}
