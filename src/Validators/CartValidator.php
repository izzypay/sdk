<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Models\Cart;
use IzzyPay\Models\CartItem;

class CartValidator
{
    /**
     * @param CartItem $cartItem
     * @return void
     * @throws InvalidCartItemException
     */
    public function validateCartItem(CartItem $cartItem): void
    {
        $errors = [];

        if (trim($cartItem->getName()) === '') {
            $errors[] = 'name';
        }

        if (($cartItem->getCategory() === null)) {
            if (!in_array(trim($cartItem->getType()), CartItem::CATEGORY_NULLABLE_TYPES, true)) {
                $errors[] = 'category';
            }
        } else if (trim($cartItem->getCategory()) === '') {
            $errors[] = 'category';
        }
        if (($cartItem->getSubCategory() !== null) && (trim($cartItem->getSubCategory()) === '')) {
            $errors[] = 'subCategory';
        }
        if (!in_array(trim($cartItem->getType()), CartItem::ALL_TYPES, true)) {
            $errors[] = 'type';
        }
        if (preg_match('/^-?\d+(\.\d{1,2})?$/', (string) $cartItem->getPrice()) === 0) {
            $errors[] = 'price';
        } else if (($cartItem->getPrice() < 0) && !in_array(trim($cartItem->getType()), CartItem::DISCOUNT_TYPES, true)) {
            $errors[] = 'price';
        }
        if ($cartItem->getQuantity() <= 0) {
            $errors[] = 'quantity';
        }
        if (($cartItem->getManufacturer() !== null) && (trim($cartItem->getManufacturer()) === '')) {
            $errors[] = 'manufacturer';
        }
        if (($cartItem->getMerchantItemId() !== null) && (trim($cartItem->getMerchantItemId()) === '')) {
            $errors[] = 'merchantItemId';
        }
        if (($cartItem->getOther() !== null) && (trim($cartItem->getOther()) === '')) {
            $errors[] = 'other';
        }

        if (count($errors) > 0) {
            throw new InvalidCartItemException($errors);
        }
    }

    /**
     * @param Cart $cart
     * @return void
     * @throws InvalidCartException
     * @throws InvalidCartItemException
     */
    public function validateCart(Cart $cart): void
    {
        $totalValue = 0;
        foreach ($cart->getItems() as $cartItem) {
            $this->validateCartItem($cartItem);
            $totalValue += round(round($cartItem->getPrice(), 2) * $cartItem->getQuantity(), 2);
        }

        $errors = [];

        if (!in_array(trim($cart->getCurrency()), Cart::ALLOWED_CURRENCIES, true)) {
            $errors[] = 'currency';
        }

        if (preg_match('/^\d+(\.\d{1,2})?$/', (string) $cart->getTotalValue()) === 0) {
            $errors[] = 'totalValue';
        } else if (($cart->getTotalValue() < 0)) {
            $errors[] = 'totalValue';
        } else if (($cart->getCurrency() === Cart::CURRENCY_HUF) && ($cart->getTotalValue() !== round($totalValue, 0))) {
            $errors[] = 'totalValue';
        }

        if (count($cart->getItems()) === 0) {
            $errors[] = 'items';
        }

        if (count($errors) > 0) {
            throw new InvalidCartException($errors);
        }
    }
}
