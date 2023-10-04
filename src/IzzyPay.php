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
use IzzyPay\Models\Cart;
use IzzyPay\Models\Customer;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Other;
use IzzyPay\Models\Response\InitResponse;
use IzzyPay\Models\Response\StartResponse;
use IzzyPay\Models\StartOther;
use IzzyPay\Models\Urls;
use IzzyPay\Validators\CartValidator;
use IzzyPay\Validators\CustomerValidator;
use IzzyPay\Validators\OtherValidator;
use IzzyPay\Validators\UrlsValidator;
use JsonException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IzzyPay extends AbstractIzzyPay
{
    public const CRED_ENDPOINT = '/api/v1/cred';
    public const INIT_ENDPOINT = '/api/v1/init';
    public const START_ENDPOINT = '/api/v1/start';
    public const DELIVERY_ENDPOINT = '/api/v1/delivery';
    public const RETURN_ENDPOINT = '/api/v1/return';

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
        $response = $this->requestService->sendPostRequest(static::INIT_ENDPOINT, $body);
        $this->responseValidator->validateInitResponse($response);
        $this->responseValidator->verifyInitAvailability($response);
        return new InitResponse($response['token'], $response['merchantId'], $response['merchantCartId'], $response['jsUrl']);
    }

    /**
     * @param string $token
     * @param string $merchantCartId
     * @param Cart $cart
     * @param Customer $customer
     * @param StartOther $other
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

        $endpoint = static::START_ENDPOINT . '/' . $token;
        $body = $this->prepareStartRequestData($merchantCartId, $cart, $customer, $other, $urls);
        $response = $this->requestService->sendPostRequest($endpoint, $body);
        $this->responseValidator->validateStartResponse($response);
        $this->responseValidator->verifyStartAvailability($response);
        return new StartResponse($response['token'], $response['merchantId'], $response['merchantCartId']);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param AbstractCustomer $customer
     * @param StartOther $other
     * @param Urls $urls
     * @return array<string, mixed>
     **/
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
