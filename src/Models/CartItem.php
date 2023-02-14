<?php

declare(strict_types=1);

namespace IzzyPay\Models;

use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Validators\CartValidator;

class CartItem
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_SERVICE = 'service';
    public const TYPE_DISCOUNT_SHIPPING = 'discount_shipping';
    public const TYPE_DISCOUNT_PAYMENT = 'discount_payment';
    public const TYPE_DISCOUNT_COUPON = 'discount_coupon';
    public const TYPE_DISCOUNT_VOLUME = 'discount_volume';
    public const TYPE_DISCOUNT_LOYALTY = 'discount_loyalty';
    public const ALL_TYPES = [
        self::TYPE_PRODUCT,
        self::TYPE_DELIVERY,
        self::TYPE_SERVICE,
        self::TYPE_DISCOUNT_SHIPPING,
        self::TYPE_DISCOUNT_PAYMENT,
        self::TYPE_DISCOUNT_COUPON,
        self::TYPE_DISCOUNT_VOLUME,
        self::TYPE_DISCOUNT_LOYALTY,
    ];
    public const DISCOUNT_TYPES = [
        self::TYPE_DISCOUNT_SHIPPING,
        self::TYPE_DISCOUNT_PAYMENT,
        self::TYPE_DISCOUNT_COUPON,
        self::TYPE_DISCOUNT_VOLUME,
        self::TYPE_DISCOUNT_LOYALTY,
    ];
    public const CATEGORY_NULLABLE_TYPES = [
        self::TYPE_DELIVERY,
        self::TYPE_DISCOUNT_SHIPPING,
        self::TYPE_DISCOUNT_PAYMENT,
        self::TYPE_DISCOUNT_COUPON,
        self::TYPE_DISCOUNT_VOLUME,
        self::TYPE_DISCOUNT_LOYALTY,
    ];

    private string $name;
    private ?string $category;
    private ?string $subCategory;
    private string $type;
    private float $price;
    private int $quantity;
    private string $manufacturer;
    private string $merchantItemId;
    private ?string $other;

    /**
     * @param string $name
     * @param string|null $category
     * @param string|null $subCategory
     * @param string $type
     * @param float $price
     * @param int $quantity
     * @param string $manufacturer
     * @param string $merchantItemId
     * @param string|null $other
     */
    private function __construct(string $name, ?string $category, ?string $subCategory, string $type, float $price, int $quantity, string $manufacturer, string $merchantItemId, ?string $other)
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
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return CartItem
     */
    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubCategory(): ?string
    {
        return $this->subCategory;
    }

    /**
     * @param string|null $subCategory
     * @return CartItem
     */
    public function setSubCategory(?string $subCategory): self
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
     * @return string|null
     */
    public function getOther(): ?string
    {
        return $this->other;
    }

    /**
     * @param string|null $other
     * @return CartItem
     */
    public function setOther(?string $other): self
    {
        $this->other = $other;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'manufacturer' => $this->manufacturer,
            'merchantItemId' => $this->merchantItemId,
        ];
        if ($this->category) {
            $data['category'] = $this->category;
        }
        if ($this->subCategory) {
            $data['subCategory'] = $this->subCategory;
        }
        if ($this->other) {
            $data['other'] = $this->other;
        }
        return $data;
    }

    /**
     * @param string $name
     * @param string|null $category
     * @param string|null $subCategory
     * @param string $type
     * @param float $price
     * @param int $quantity
     * @param string $manufacturer
     * @param string $merchantItemId
     * @param string|null $other
     * @return static
     * @throws InvalidCartItemException
     */
    public static function create(string $name, ?string $category, ?string $subCategory, string $type, float $price, int $quantity, string $manufacturer, string $merchantItemId, ?string $other): self
    {
        $cartItem = new CartItem($name, $category, $subCategory, $type, $price, $quantity, $manufacturer, $merchantItemId, $other);

        $cartValidator = new CartValidator();
        $cartValidator->validateCartItem($cartItem);

        return $cartItem;
    }
}
