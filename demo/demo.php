<?php

declare(strict_types=1);

require_once('vendor/autoload.php');

use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\IzzyPay;
use IzzyPay\Models\Address;
use IzzyPay\Models\CartItem;
use IzzyPay\Models\Cart;
use IzzyPay\Models\BasicCustomer;
use IzzyPay\Models\DetailedCustomer;
use IzzyPay\Models\Other;
use GuzzleHttp\Exception\GuzzleException;

try {
    $merchantCartId = '666';
    $izzyPay = new IzzyPay('merchantId', 'merchantSecret', 'http://localhost:3333');
    $izzyPay->cred();

    $cartItem = CartItem::create('name','category', 'subCategory', 'type', 666.666, 69, 'manufacturer', 'merchantItemId', 'other');
    $cart = Cart::create('HUF', 666.666, [$cartItem]);
    $cart->reset();
    $cart->addItem($cartItem);
    $basicCustomer = BasicCustomer::create('guest', 'merchantCustomerId', 'other');
    $other = Other::create('127.0.0.1', 'browser', 'os');
    $izzyPay->init($merchantCartId, $cart, $basicCustomer, $other);

    $address = Address::create('8888', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3');
    $detailedCustomer = DetailedCustomer::create('guest', 'merchantCustomerId', 'other', 'name', 'surname', 'phone', 'email', $address, $address);
    $izzyPay->start($merchantCartId, $cart, $detailedCustomer, $other);
} catch (InvalidCustomerException $e) {
    var_dump($e->getMessage());
} catch (InvalidCartException $e) {
    var_dump($e->getMessage());
} catch (InvalidOtherException $e) {
    var_dump($e->getMessage());
} catch (InvalidResponseException $e) {
    var_dump($e->getMessage());
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
} catch (JsonException $e) {
    var_dump($e->getMessage());
}
