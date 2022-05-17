<?php

declare(strict_types=1);

namespace IzzyPay;

use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\BasicCustomer;
use IzzyPay\Models\Cart;
use IzzyPay\Models\DetailedCustomer;
use IzzyPay\Models\Other;
use IzzyPay\Services\RequestService;
use JsonException;

class IzzyPay
{
    private string $merchantId;
    private RequestService $requestService;

    public function __construct(string $merchantId, string $merchantSecret, string $baseUrl)
    {
        $this->merchantId = $merchantId;
        $this->requestService = new RequestService($merchantId, $merchantSecret, $baseUrl);
    }

    /**
     * @throws InvalidResponseException
     * @throws RequestException
     */
    public function cred(): void
    {
        $this->requestService->sendHeadRequest(RequestService::CRED_ENDPOINT);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param BasicCustomer $customer
     * @param Other $other
     * @return array
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws RequestException
     */
    public function init(string $merchantCartId, Cart $cart, BasicCustomer $customer, Other $other): array
    {
        $body = $this->prepareRequestData($merchantCartId, $cart, $customer, $other);
        return $this->requestService->sendPostRequest(RequestService::INIT_ENDPOINT, $body);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param DetailedCustomer $customer
     * @param Other $other
     * @return array
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws RequestException
     */
    public function start(string $merchantCartId, Cart $cart, DetailedCustomer $customer, Other $other): array
    {
        $body = $this->prepareRequestData($merchantCartId, $cart, $customer, $other);
        return $this->requestService->sendPostRequest(RequestService::START_ENDPOINT, $body);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param AbstractCustomer $customer
     * @param Other $other
     * @return array
     */
    private function prepareRequestData(string $merchantCartId, Cart $cart, AbstractCustomer $customer, Other $other): array
    {
        return [
            'merchantId' => $this->merchantId,
            'merchantCartId' => $merchantCartId,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
        ];
    }
}
