<?php

declare(strict_types=1);

require('IzzyPay.php');
require('Models\BasicCustomer.php');
require('Models\Address.php');
require('Models\CartItem.php');
require('Models\Cart.php');
require('Models\Other.php');

use Bnpl\Exception\InvalidAddressException;
use Bnpl\Exception\InvalidResponseException;
use Bnpl\IzzyPay;
use Bnpl\Models\Address;
use Bnpl\Models\CartItem;
use Bnpl\Models\Cart;
use Bnpl\Models\BasicCustomer;
use Bnpl\Models\Other;
use GuzzleHttp\Exception\GuzzleException;

try {
    $merchantCartId = '666';
    $address = Address::create('zip', 'city', 'street', 'houseNo', 'address1', 'address2', 'address3');
    $customer = new BasicCustomer('registered', 'merchantCustomerId', 'other');
    $cartItem = new CartItem('name', 666.666, 69);
    $cart = new Cart('HUF');
    $cart->addItem($cartItem);
    $other = new Other('ip', 'browser', 'os');
    $izzyPay = new IzzyPay('merchantId', 'merchantSecret', 'https://www.izzypay.hu/bnpl');
    $izzyPay->cred();
    $izzyPay->init($merchantCartId, $cart, $customer, $other);
} catch (InvalidAddressException $e) {
} catch (InvalidResponseException $e) {
} catch (GuzzleException $e) {
} catch (JsonException $e) {
}
