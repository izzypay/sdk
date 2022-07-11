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
     * @param string|null $currency
     * @return bool
     */
    private function validateCurrency(?string $currency): bool
    {
        return $currency === 'HUF';
    }

    /**
     * @param int $quantity
     * @return bool
     */
    private function validateQuantity(int $quantity): bool
    {
        return $quantity > 0;
    }

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

        if ((trim($cartItem->getType()) !== CartItem::TYPE_DELIVERY) && trim($cartItem->getCategory()) === '') {
            $errors[] = 'category';
        }

        if (trim($cartItem->getType()) === '') {
            $errors[] = 'type';
        }

        if (!$this->validateQuantity($cartItem->getQuantity())) {
            $errors[] = 'quantity';
        }

        if (trim($cartItem->getMerchantItemId()) === '') {
            $errors[] = 'merchantItemId';
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
        foreach ($cart->getItems() as $cartItem) {
            $this->validateCartItem($cartItem);
        }

        $errors = [];

        if (!$this->validateCurrency($cart->getCurrency())) {
            $errors[] = 'currency';
        }

        if ($cart->getTotalValue() < 0) {
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
