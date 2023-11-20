<?php

declare(strict_types=1);

require_once('vendor/autoload.php');

use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\InvalidReturnDataException;
use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Exceptions\PaymentServiceUnavailableException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Models\Address;
use IzzyPay\Models\CartItem;
use IzzyPay\Models\Cart;
use IzzyPay\Models\CreateOther;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Customer;
use IzzyPay\Models\Other;
use IzzyPay\Models\RedirectUrls;
use IzzyPay\Models\Response\CreateResponse;
use IzzyPay\Models\Response\RedirectInitResponse;
use IzzyPay\RedirectIzzyPay;

$merchantCartId = '8';
$izzyPay = new RedirectIzzyPay('merchant1', 'abcd1234', 'https://test.izzpay.hu', 'plugin 1.0');

function verifyCredential(RedirectIzzyPay $izzyPay): void
{
    try {
        $izzyPay->cred();
    } catch (RequestException|AuthenticationException $e) {
        var_dump($e->getMessage());
    }
}

function sendInit(RedirectIzzyPay $izzyPay, string $merchantCartId): ?RedirectInitResponse
{
    try {
        $cartItem = CartItem::create('name','category', 'subCategory', 'product', 6666.6, 1, 'manufacturer', 'merchantItemId', 'other');
        $cart = Cart::create('HUF', 6667.00, [$cartItem]);
        $limitedCustomer = LimitedCustomer::create('guest', null, null, 'other');
        $other = Other::create('browser');
        return $izzyPay->init($merchantCartId, $cart, $limitedCustomer, $other);
    } catch (InvalidCustomerException|InvalidCartItemException|InvalidCartException|InvalidOtherException|InvalidResponseException|RequestException|JsonException|AuthenticationException|PaymentServiceUnavailableException $e) {
        var_dump($e->getMessage());
    }
    return null;
}

function sendCreate(RedirectIzzyPay $izzyPay, string $merchantCartId, ?string $token): ?CreateResponse
{
    try {
        $cartItem = CartItem::create('name','category', 'subCategory', 'product', 6666.66, 1, 'manufacturer', 'merchantItemId', 'other');
        $cart = Cart::create('HUF', 6667.00, [$cartItem]);
        $address = Address::create('8888', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3');
        $customer = Customer::create('merchant', 'merchantCustomerId', null,'other', 'name', 'surname', 'phone', 'email@emai.com', $address, $address);
        $other = CreateOther::create('127.0.0.1', 'browser');
        $urls = RedirectUrls::create('https://webshop.url/accepted', 'https://webshop.url/rejected', 'https://webshop.url/cancelled', 'https://webshop.url/ipn', 'https://webshop.url/checkout');
        return $izzyPay->create($token, $merchantCartId, $cart, $customer, $other, $urls);
    } catch (InvalidAddressException|InvalidCustomerException|InvalidCartItemException|InvalidCartException|InvalidOtherException|InvalidResponseException|RequestException|JsonException|InvalidUrlsException|AuthenticationException|PaymentServiceUnavailableException $e) {
        var_dump($e->getMessage());
    }
    return null;
}

function sendDeliveryCart(RedirectIzzyPay $izzyPay, string $merchantCartId): void
{
    try {
        $izzyPay->deliveryCart($merchantCartId);
    } catch (AuthenticationException|RequestException|JsonException $e) {
        var_dump($e->getMessage());
    }
}

function sendDeliveryItem(RedirectIzzyPay $izzyPay, string $merchantCartId, string $merchantItemId): void
{
    try {
        $izzyPay->deliveryItem($merchantCartId, $merchantItemId);
    } catch (AuthenticationException|RequestException|JsonException $e) {
        var_dump($e->getMessage());
    }
}

function sendReturnCart(RedirectIzzyPay $izzyPay, string $merchantCartId, DateTimeImmutable $returnDate): void
{
    try {
        $izzyPay->returnCart($merchantCartId, $returnDate);
    } catch (AuthenticationException|RequestException|JsonException $e) {
        var_dump($e->getMessage());
    }
}

function sendReturnItem(RedirectIzzyPay $izzyPay, string $merchantCartId, string $merchantItemId, DateTimeImmutable $returnDate, ?float $reducedValue = null): void
{
    try {
        $izzyPay->returnItem($merchantCartId, $merchantItemId, $returnDate, $reducedValue);
    } catch (AuthenticationException|RequestException|JsonException|InvalidReturnDataException $e) {
        var_dump($e->getMessage());
    }
}

// Used to check whether the configured credentials are correct.
// Not part of the normal flow, therefore doesn't need to be called before the init.
verifyCredential($izzyPay);

$token = null;
$initResponse = sendInit($izzyPay, $merchantCartId);
if ($initResponse) {
    $token = $initResponse->getToken();
    var_dump($token);
}

$createResponse = sendCreate($izzyPay, $merchantCartId, $token);
if ($createResponse) {
    $token = $createResponse->getToken();
    var_dump($createResponse->getRedirectUrl());

    // Delivery for the whole cart
    sendDeliveryCart($izzyPay, $merchantCartId);
    // Delivery for single item from the cart
    sendDeliveryItem($izzyPay, $merchantCartId, 'merchantItemId');

    // Return the whole cart
    sendReturnCart($izzyPay, $merchantCartId, new DateTimeImmutable('2022-04-04T12:34:56+0010'));
    // Return single item from the cart
    sendReturnItem($izzyPay, $merchantCartId, 'merchantItemId', new DateTimeImmutable('2022-04-04T12:34:56+0010'), 100.2);
}

// Helper to validate authentication on IPN calls
try {
    $contentArray = [
        'token' => 'b752c5e0-fd6e-4fcb-a194-0d2691a79403',
        'merchantId' => '1',
        'merchantCartId' => $merchantCartId,
        'status' => 'accepted',
        'submitDate' => '2023-10-24 10:00:00',
        'finishDate' => '2023-10-24 10:10:00'
    ];
    $content = json_encode($contentArray, JSON_THROW_ON_ERROR);
    $signature = base64_encode(hash_hmac('sha384', $content, 'abcd1234', true));
    $authorizationHeader = "HMAC $signature";

    $izzyPay->validateAuthentication($content, $authorizationHeader);
} catch (AuthenticationException|JsonException $e) {
    var_dump($e->getMessage());
}
