<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Models\Cart;
use IzzyPay\Models\CartItem;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CartTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const CURRENCY = 'HUF';
    private const TOTAL_VALUE = 666.666;
    private const NAME = 'name';
    private const CATEGORY = 'category';
    private const SUB_CATEGORY = 'subCategory';
    private const TYPE = 'type';
    private const PRICE = 666.666;
    private const QUANTITY = 69;
    private const MANUFACTURER = 'manufacturer';
    private const MERCHANT_ITEM_ID = 'merchantItemId';
    private const OTHER = 'Other';

    protected function setUp(): void
    {
        $this->fields = [
            'currency' => 'currency',
            'totalValue' => 666.69,
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $cart = $this->invokeConstructor(Cart::class, [self::CURRENCY, self::TOTAL_VALUE]);
        $this->_testSettersAndGetters($cart);
    }

    /**
     * @throws ReflectionException
     * @throws InvalidCartException
     */
    public function testAddItem(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = $this->invokeConstructor(Cart::class, [self::CURRENCY, self::TOTAL_VALUE]);
        $cart->addItem($cartItem);
        $this->assertEquals([$cartItem], $cart->getItems());
    }

    /**
     * @throws ReflectionException
     * @throws InvalidCartException
     */
    public function testReset(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = $this->invokeConstructor(Cart::class, [self::CURRENCY, self::TOTAL_VALUE]);
        $cart->addItem($cartItem);
        $cart->reset();
        $this->assertEquals([], $cart->getItems());
    }

    /**
     * @throws ReflectionException
     * @throws InvalidCartException
     * @throws InvalidCartItemException
     */
    public function testToArray(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = $this->invokeConstructor(Cart::class, [self::CURRENCY, self::TOTAL_VALUE]);
        $cart->addItem($cartItem);
        $cartAsArray = $cart->toArray();
        $this->assertEqualsCanonicalizing([
            'currency' => self::CURRENCY,
            'totalValue' => self::TOTAL_VALUE,
            'items' => [
                [
                    'name' => self::NAME,
                    'category' => self::CATEGORY,
                    'subCategory' => self::SUB_CATEGORY,
                    'type' => self::TYPE,
                    'price' => self::PRICE,
                    'quantity' => self::QUANTITY,
                    'manufacturer' => self::MANUFACTURER,
                    'merchantItemId' => self::MERCHANT_ITEM_ID,
                    'other' => self::OTHER,
                ]
            ],
        ], $cartAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidCartException::class);
        Cart::create('invalid', -666.666);
    }

    /**
     * @throws InvalidCartException
     * @throws InvalidCartItemException
     */
    public function testCreate(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $this->assertEquals(self::CURRENCY, $cart->getCurrency());
        $this->assertEquals(self::TOTAL_VALUE, $cart->getTotalValue());
    }
}
