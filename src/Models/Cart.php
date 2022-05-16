<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Validators\CartValidator;

class Cart
{
    private string $currency;
    private float $totalValue;
    /**
     * @var CartItem[]
     */
    private array $items;

    private function __construct(string $currency, float $totalValue)
    {
        $this->currency = $currency;
        $this->totalValue = $totalValue;
        $this->items = [];
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Cart
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalValue(): float
    {
        return $this->totalValue;
    }

    /**
     * @param float $totalValue
     * @return Cart
     */
    public function setTotalValue(float $totalValue): self
    {
        $this->totalValue = $totalValue;
        return $this;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param CartItem $item
     * @return Cart
     */
    public function addItem(CartItem $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @return Cart
     */
    public function reset(): self
    {
        $this->items = [];
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item->toArray();
        }

        return [
            'currency' => $this->currency,
            'totalValue' => $this->totalValue,
            'items' => $items,
        ];
    }

    /**
     * @param string $currency
     * @param float $totalValue
     * @param array $cartItems
     * @return static
     * @throws InvalidCartException
     */
    public static function create(string $currency, float $totalValue, array $cartItems = []): self
    {
        $cart = new Cart($currency, $totalValue);
        foreach ($cartItems as $cartItem) {
            $cart->addItem($cartItem);
        }

        $cartValidator = new CartValidator();
        $invalidFields = $cartValidator->validateCart($cart);
        if (count($invalidFields) > 0) {
            throw new InvalidCartException($invalidFields);
        }

        return $cart;
    }
}
