<?php

declare(strict_types=1);

namespace Bnpl\Models;

use Bnpl\Exception\InvalidCartException;
use Bnpl\Validators\CartValidator;

class CartItem
{
    private string $name;
    private string $category;
    private string $subCategory;
    private string $type;
    private float $price;
    private int $quantity;
    private string $manufacturer;
    private string $merchantItemId;
    private string $other;

    /**
     * @param string $name
     * @param string $category
     * @param string $subCategory
     * @param string $type
     * @param float $price
     * @param int $quantity
     * @param string $manufacturer
     * @param string $merchantItemId
     * @param string $other
     */
    private function __construct(string $name, string $category, string $subCategory, string $type, float $price, int $quantity, string $manufacturer, string $merchantItemId, string $other)
    {
        $this->name = $name;
        $this->category = $category;
        $this->subCategory = $subCategory;
        $this->type = $type;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->manufacturer = $manufacturer;
        $this->merchantItemId = $merchantItemId;
        $this->other = $other;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CartItem
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return CartItem
     */
    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubCategory(): string
    {
        return $this->subCategory;
    }

    /**
     * @param string $subCategory
     * @return CartItem
     */
    public function setSubCategory(string $subCategory): self
    {
        $this->subCategory = $subCategory;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return CartItem
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }


    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return CartItem
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return CartItem
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return string
     */
    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    /**
     * @param string $manufacturer
     * @return CartItem
     */
    public function setManufacturer(string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantItemId(): string
    {
        return $this->merchantItemId;
    }

    /**
     * @param string $merchantItemId
     * @return CartItem
     */
    public function setMerchantItemId(string $merchantItemId): self
    {
        $this->merchantItemId = $merchantItemId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOther(): string
    {
        return $this->other;
    }

    /**
     * @param string $other
     * @return CartItem
     */
    public function setOther(string $other): self
    {
        $this->other = $other;
        return $this;
    }

    /**
     * @param string $name
     * @param string $category
     * @param string $subCategory
     * @param string $type
     * @param float $price
     * @param int $quantity
     * @param string $manufacturer
     * @param string $merchantItemId
     * @param string $other
     * @return static
     * @throws InvalidCartException
     */
    public static function create(string $name, string $category, string $subCategory, string $type, float $price, int $quantity, string $manufacturer, string $merchantItemId, string $other): self
    {
        $cartItem = new CartItem($name, $category, $subCategory, $type, $price, $quantity, $manufacturer, $merchantItemId, $other);

        $cartValidator = new CartValidator();
        $invalidFields = $cartValidator->validateCartItem($cartItem);
        if (count($invalidFields) > 0) {
            throw new InvalidCartException($invalidFields);
        }

        return $cartItem;
    }
}
