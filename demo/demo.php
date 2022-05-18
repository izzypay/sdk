<?php

declare(strict_types=1);

require_once('vendor/autoload.php');

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\IzzyPay;
use IzzyPay\Models\Address;
use IzzyPay\Models\CartItem;
use IzzyPay\Models\Cart;
use IzzyPay\Models\BasicCustomer;
use IzzyPay\Models\DetailedCustomer;
use IzzyPay\Models\Other;
use IzzyPay\Models\Urls;

$token = null;
$merchantCartId = '666';
$izzyPay = new IzzyPay('merchantId', 'abcd1234', 'http://gateway.localhost');

function verifyCredential(IzzyPay $izzyPay): void
{
    try {
        $izzyPay->cred();
    } catch (InvalidResponseException|RequestException $e) {
        var_dump($e->getMessage());
    }
}

function sendInit(IzzyPay $izzyPay, string $merchantCartId): ?array
{
    try {
        $cartItem = CartItem::create('name','category', 'subCategory', 'type', 666.666, 69, 'manufacturer', 'merchantItemId', 'other');
        $cart = Cart::create('HUF', 666.666, [$cartItem]);
        $basicCustomer = BasicCustomer::create('guest', 'merchantCustomerId', 'other');
        $other = Other::create('127.0.0.1', 'browser', 'os');
        return $izzyPay->init($merchantCartId, $cart, $basicCustomer, $other);
    } catch (InvalidCustomerException|InvalidCartException|InvalidOtherException|InvalidResponseException|RequestException|JsonException $e) {
        var_dump($e->getMessage());
    }
    return null;
}

function sendStart(IzzyPay $izzyPay, string $merchantCartId, $token): ?array
{
    try {
        $cartItem = CartItem::create('name','category', 'subCategory', 'type', 666.666, 69, 'manufacturer', 'merchantItemId', 'other');
        $cart = Cart::create('HUF', 666.666, [$cartItem]);
        $address = Address::create('8888', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3');
        $detailedCustomer = DetailedCustomer::create('guest', 'merchantCustomerId', 'other', 'name', 'surname', 'phone', 'email@emai.com', $address, $address);
        $other = Other::create('127.0.0.1', 'browser', 'os');
        $urls = Urls::create('https://ipn.com');
        return $izzyPay->start($token, $merchantCartId, $cart, $detailedCustomer, $other, $urls);
    } catch (InvalidCustomerException|InvalidCartException|InvalidOtherException|InvalidResponseException|RequestException|JsonException|InvalidUrlsException $e) {
        var_dump($e->getMessage());
    }
    return null;
}

verifyCredential($izzyPay);
$initResponse = sendInit($izzyPay, $merchantCartId);
if ($initResponse && !array_key_exists('errors', $initResponse) && $initResponse['available']) {
    $jsUrl = $initResponse['jsUrl'];
    $token = $initResponse['token'];
    var_dump($jsUrl);
} else {
    var_dump($initResponse['errors']);
}

if ($token) {
    $startResponse = sendStart($izzyPay, $merchantCartId, $token);
    if ($startResponse && !array_key_exists('errors', $startResponse)) {
        var_dump('Ok');
    } else {
        var_dump($startResponse['errors']);
    }
}
