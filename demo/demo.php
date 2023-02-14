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
use IzzyPay\IzzyPay;
use IzzyPay\Models\Address;
use IzzyPay\Models\CartItem;
use IzzyPay\Models\Cart;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Customer;
use IzzyPay\Models\Other;
use IzzyPay\Models\Response\InitResponse;
use IzzyPay\Models\Response\StartResponse;
use IzzyPay\Models\StartOther;
use IzzyPay\Models\Urls;

$merchantCartId = '666';
$izzyPay = new IzzyPay('1', 'abcd1234', 'http://gatewaydmz.localhost', 'plugin 1.0');

function verifyCredential(IzzyPay $izzyPay): void
{
    try {
        $izzyPay->cred();
    } catch (RequestException|AuthenticationException $e) {
        var_dump($e->getMessage());
    }
}

function sendInit(IzzyPay $izzyPay, string $merchantCartId): ?InitResponse
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

function sendStart(IzzyPay $izzyPay, string $merchantCartId, $token): ?StartResponse
{
    try {
        $cartItem = CartItem::create('name','category', 'subCategory', 'product', 6666.66, 1, 'manufacturer', 'merchantItemId', 'other');
        $cart = Cart::create('HUF', 6667.00, [$cartItem]);
        $address = Address::create('8888', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3');
        $customer = Customer::create('merchant', 'merchantCustomerId', null,'other', 'name', 'surname', 'phone', 'email@emai.com', $address, $address);
        $other = StartOther::create('127.0.0.1', 'browser');
        $urls = Urls::create('https://ipn.com', 'https://checkout.com');
        return $izzyPay->start($token, $merchantCartId, $cart, $customer, $other, $urls);
    } catch (InvalidAddressException|InvalidCustomerException|InvalidCartItemException|InvalidCartException|InvalidOtherException|InvalidResponseException|RequestException|JsonException|InvalidUrlsException|AuthenticationException|PaymentServiceUnavailableException $e) {
        var_dump($e->getMessage());
    }
    return null;
}

function sendDeliveryCart(IzzyPay $izzyPay, string $merchantCartId): void
{
    try {
        $izzyPay->deliveryCart($merchantCartId);
    } catch (AuthenticationException|RequestException|JsonException $e) {
        var_dump($e->getMessage());
    }
}

function sendDeliveryItem(IzzyPay $izzyPay, string $merchantCartId, string $merchantItemId): void
{
    try {
        $izzyPay->deliveryItem($merchantCartId, $merchantItemId);
    } catch (AuthenticationException|RequestException|JsonException $e) {
        var_dump($e->getMessage());
    }
}

function sendReturnCart(IzzyPay $izzyPay, string $merchantCartId, string $returnDate): void
{
    try {
        $izzyPay->returnCart($merchantCartId, $returnDate);
    } catch (AuthenticationException|RequestException|JsonException|InvalidReturnDataException $e) {
        var_dump($e->getMessage());
    }
}

function sendReturnItem(IzzyPay $izzyPay, string $merchantCartId, string $merchantItemId, string $returnDate, ?float $reducedValue = null): void
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

$initResponse = sendInit($izzyPay, $merchantCartId);
if ($initResponse) {
    $jsUrl = $initResponse->getJsUrl();
    $token = $initResponse->getToken();
    var_dump($jsUrl);

    $startResponse = sendStart($izzyPay, $merchantCartId, $token);
    if ($startResponse) {
        $token = $startResponse->getToken();
        var_dump('Ok');

        // Delivery for the whole cart
        sendDeliveryCart($izzyPay, $merchantCartId);
        // Delivery for single item from the cart
        sendDeliveryItem($izzyPay, $merchantCartId, 'merchantItemId');

        // Return the whole cart
        sendReturnCart($izzyPay, $merchantCartId, '2022-04-04T12:34:56+0010');
        // Return single item from the cart
        sendReturnItem($izzyPay, $merchantCartId, 'merchantItemId', '2022-04-04T12:34:56+0010', 100.2);
    }
}
