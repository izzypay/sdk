<?php

declare(strict_types=1);

require('IzzyPay.php');
require('Models\BasicCustomer.php');
require('Models\Address.php');
require('Models\CartItem.php');
require('Models\Cart.php');
require('Models\Other.php');

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
    $izzyPay = new IzzyPay('merchantId', 'merchantSecret', 'https://www.izzypay.hu/bnpl');
    $izzyPay->cred();

    $cartItem = CartItem::create('name','category', 'subCategory', 'type', 666.666, 69, 'manufacturer', 'merchantItemId', 'other');
    $cart = Cart::create('HUF', 666.666, [$cartItem]);
    $cart->reset();
    $cart->addItem($cartItem);
    $basicCustomer = BasicCustomer::create('registered', 'merchantCustomerId', 'other');
    $other = Other::create('ip', 'browser', 'os');
    $izzyPay->init($merchantCartId, $cart, $basicCustomer, $other);

    $address = Address::create('zip', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3');
    $detailedCustomer = DetailedCustomer::create('registered', 'merchantCustomerId', 'other', 'name', 'surname', 'phone', 'email', $address, $address);
    $izzyPay->start($merchantCartId, $cart, $detailedCustomer, $other);
} catch (InvalidCustomerException $e) {
} catch (InvalidCartException $e) {
} catch (InvalidOtherException $e) {
} catch (InvalidResponseException $e) {
} catch (GuzzleException $e) {
} catch (JsonException $e) {
}
