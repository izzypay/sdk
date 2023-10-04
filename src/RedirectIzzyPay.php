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
use IzzyPay\Models\CreateOther;
use IzzyPay\Models\Customer;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Other;
use IzzyPay\Models\RedirectUrls;
use IzzyPay\Models\Response\CreateResponse;
use IzzyPay\Models\Response\RedirectInitResponse;
use IzzyPay\Validators\CartValidator;
use IzzyPay\Validators\CustomerValidator;
use IzzyPay\Validators\OtherValidator;
use IzzyPay\Validators\RedirectUrlsValidator;
use JsonException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectIzzyPay extends AbstractIzzyPay
{
    public const CRED_ENDPOINT = '/api/r1/cred';
    public const INIT_ENDPOINT = '/api/r1/init';
    public const CREATE_ENDPOINT = '/api/r1/create';
    public const DELIVERY_ENDPOINT = '/api/r1/delivery';
    public const RETURN_ENDPOINT = '/api/r1/return';

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param LimitedCustomer $limitedCustomer
     * @param Other $other
     * @return RedirectInitResponse
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
    public function init(string $merchantCartId, Cart $cart, LimitedCustomer $limitedCustomer, Other $other): RedirectInitResponse
    {
        $cartValidator = new CartValidator();
        $cartValidator->validateCart($cart);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateLimitedCustomer($limitedCustomer);

        $otherValidator = new OtherValidator();
        $otherValidator->validateOther($other);

        $body = $this->prepareInitRequestData($merchantCartId, $cart, $limitedCustomer, $other);
        $response = $this->requestService->sendPostRequest(static::INIT_ENDPOINT, $body);
        $this->responseValidator->validateRedirectInitResponse($response);
        $this->responseValidator->verifyInitAvailability($response);
        return new RedirectInitResponse($response['token'], $response['merchantId'], $response['merchantCartId']);
    }

    /**
     * @param string|null $token
     * @param string $merchantCartId
     * @param Cart $cart
     * @param Customer $customer
     * @param CreateOther $other
     * @param RedirectUrls $urls
     * @return CreateResponse
     * @throws AuthenticationException
     * @throws InvalidAddressException
     * @throws InvalidCartException
     * @throws InvalidCartItemException
     * @throws InvalidCustomerException
     * @throws InvalidOtherException
     * @throws InvalidResponseException
     * @throws InvalidUrlsException
     * @throws JsonException
     * @throws PaymentServiceUnavailableException
     * @throws RequestException
     */
    public function create(
        ?string $token,
        string $merchantCartId,
        Cart $cart,
        Customer $customer,
        CreateOther $other,
        RedirectUrls $urls
    ): CreateResponse {
        $cartValidator = new CartValidator();
        $cartValidator->validateCart($cart);

        $customerValidator = new CustomerValidator();
        $customerValidator->validateCustomer($customer);

        $otherValidator = new OtherValidator();
        $otherValidator->validateStartOther($other);

        $urlsValidator = new RedirectUrlsValidator();
        $urlsValidator->validateUrls($urls);

        $endpoint = static::CREATE_ENDPOINT . ($token ? '/' . $token : '');
        $body = $this->prepareCreateRequestData($merchantCartId, $cart, $customer, $other, $urls);
        $response = $this->requestService->sendPostRequest($endpoint, $body);
        $this->responseValidator->validateCreateResponse($response);
        $this->responseValidator->verifyCreateAvailability($response);
        return new CreateResponse($response['token'], $response['merchantId'], $response['merchantCartId'], $response['redirectUrl']);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param AbstractCustomer $customer
     * @param CreateOther $other
     * @param RedirectUrls $urls
     * @return array<string, mixed>
     **/
    private function prepareCreateRequestData(
        string $merchantCartId,
        Cart $cart,
        AbstractCustomer $customer,
        CreateOther $other,
        RedirectUrls $urls
    ): array {
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
