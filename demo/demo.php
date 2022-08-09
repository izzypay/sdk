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
use IzzyPay\Models\Urls;

$merchantCartId = '666';
$izzyPay = new IzzyPay('merchantId', 'abcd1234', 'http://gateway.localhost');

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
        $cartItem = CartItem::create('name','category', 'subCategory', 'type', 666.666, 69, 'manufacturer', 'merchantItemId', 'other');
        $cart = Cart::create('HUF', 666.666, [$cartItem]);
        $limitedCustomer = LimitedCustomer::create('guest', null, null, 'other');
        $other = Other::create('127.0.0.1', 'browser', 'os');
        return $izzyPay->init($merchantCartId, $cart, $limitedCustomer, $other);
    } catch (InvalidCustomerException|InvalidCartItemException|InvalidCartException|InvalidOtherException|InvalidResponseException|RequestException|JsonException|AuthenticationException|PaymentServiceUnavailableException $e) {
        var_dump($e->getMessage());
    }
    return null;
}

function sendStart(IzzyPay $izzyPay, string $merchantCartId, $token): ?StartResponse
{
    try {
        $cartItem = CartItem::create('name','category', 'subCategory', 'type', 666.666, 69, 'manufacturer', 'merchantItemId', 'other');
        $cart = Cart::create('HUF', 666.666, [$cartItem]);
        $address = Address::create('8888', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3');
        $customer = Customer::create('merchant', 'merchantCustomerId', null,'other', 'name', 'surname', 'phone', 'email@emai.com', $address, $address);
        $other = Other::create('127.0.0.1', 'browser', 'os');
        $urls = Urls::create('https://ipn.com');
        return $izzyPay->start($token, $merchantCartId, $cart, $customer, $other, $urls);
    } catch (InvalidAddressException|InvalidCustomerException|InvalidCartItemException|InvalidCartException|InvalidOtherException|InvalidResponseException|RequestException|JsonException|InvalidUrlsException|AuthenticationException|PaymentServiceUnavailableException $e) {
        var_dump($e->getMessage());
    }
    return null;
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
    }
}
