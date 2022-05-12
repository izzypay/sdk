<?php

declare(strict_types=1);

namespace Bnpl\Models;

class Cart
{
    private string $currency;
    private float $totalValue;
    private array $items;

    public function __construct(string $currency)
    {
        $this->currency = $currency;
        $this->totalValue = 0;
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
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
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
     * @return void
     */
    public function addItem(CartItem $item): void
    {
        $items[] = $item;
        $this->totalValue += $item->getQuantity() * $item->getPrice();
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->items = [];
    }
}
