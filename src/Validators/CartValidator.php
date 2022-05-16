<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

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
        return $quantity < 1;
    }

    /**
     * @param CartItem $cartItem
     * @return array
     */
    public function validateCartItem(CartItem $cartItem): array
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

        return $errors;
    }

    /**
     * @param Cart $cart
     * @return array
     */
    public function validateCart(Cart $cart): array
    {
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

        foreach ($cart->getItems() as $cartItem) {
            $cartItemErrors = $this->validateCartItem($cartItem);
            foreach ($cartItemErrors as $cartItemError) {
                $errors[] = $cartItemError;
            }
        }

        return $errors;
    }
}
