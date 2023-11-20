<?php

declare(strict_types=1);

namespace IzzyPay;

use DateTimeImmutable;
use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidReturnDataException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Cart;
use IzzyPay\Models\Other;
use IzzyPay\Services\HmacService;
use IzzyPay\Services\RequestService;
use IzzyPay\Validators\ResponseValidator;
use IzzyPay\Validators\ReturnValidator;
use JsonException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractIzzyPay
{
    public const CRED_ENDPOINT = '';
    public const INIT_ENDPOINT = '';
    public const DELIVERY_ENDPOINT = '';
    public const RETURN_ENDPOINT = '';

    protected string $merchantId;
    protected ResponseValidator $responseValidator;
    protected RequestService $requestService;

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
        $this->requestService->sendHeadRequest(static::CRED_ENDPOINT);
    }

    /**
     * @param string $merchantCartId
     * @throws AuthenticationException
     * @throws JsonException
     * @throws RequestException
     */
    public function deliveryCart(string $merchantCartId): void
    {
        $endpoint = static::DELIVERY_ENDPOINT . "/$this->merchantId/$merchantCartId";
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
        $endpoint = static::DELIVERY_ENDPOINT . "/$this->merchantId/$merchantCartId/$merchantItemId";
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
        $endpoint = static::RETURN_ENDPOINT . "/$this->merchantId/$merchantCartId";
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

        $endpoint = static::RETURN_ENDPOINT . "/$this->merchantId/$merchantCartId/$merchantItemId";
        $data = [
            'returnDate' => $returnDate->format(DateTimeImmutable::ISO8601),
        ];
        if ($reducedValue) {
            $data['reducedValue'] = $reducedValue;
        }
        $this->requestService->sendPutRequest($endpoint, $data);
    }

    /**
     * @param string $content
     * @param string $authorizationHeader
     * @return void
     * @throws AuthenticationException
     */
    public function validateAuthentication(string $content, string $authorizationHeader): void
    {
        $this->requestService->validateAuthentication($content, $authorizationHeader);
    }

    /**
     * @param string $content
     * @return string
     */
    public function generateAuthorizationHeader(string $content): string
    {
        return $this->requestService->generateAuthorizationHeader($content);
    }

    /**
     * @param string $merchantCartId
     * @param Cart $cart
     * @param AbstractCustomer $customer
     * @param Other $other
     * @return array<string, mixed>
     */
    protected function prepareInitRequestData(string $merchantCartId, Cart $cart, AbstractCustomer $customer, Other $other): array
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
