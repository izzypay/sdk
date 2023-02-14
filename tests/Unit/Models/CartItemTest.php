<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Models;

use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Models\CartItem;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Tests\Helpers\Traits\SetterAndGetterTesterTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CartItemTest extends TestCase
{
    use InvokeConstructorTrait;
    use SetterAndGetterTesterTrait;

    private const NAME = 'name';
    private const CATEGORY = 'category';
    private const SUB_CATEGORY = 'subCategory';
    private const TYPE = 'product';
    private const PRICE = 666.66;
    private const QUANTITY = 69;
    private const MANUFACTURER = 'manufacturer';
    private const MERCHANT_ITEM_ID = 'merchantItemId';
    private const OTHER = 'Other';

    protected function setUp(): void
    {
        $this->fields = [
            'name' => 'change name',
            'category' => 'change category',
            'subCategory' => 'change sub category',
            'type' => 'change type',
            'price' => 666.69,
            'quantity' => 666,
            'manufacturer' => 'change manufacturer',
            'merchantItemId' => 'change merchant item id',
            'other' => 'change other',
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testSettersAndGetters(): void
    {
        $cartItem = $this->invokeConstructor(CartItem::class, [self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER]);
        $this->_testSettersAndGetters($cartItem);
    }

    /**
     * @throws ReflectionException
     */
    public function testToArray(): void
    {
        $cartItem = $this->invokeConstructor(CartItem::class, [self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER]);
        $cartItemAsArray = $cartItem->toArray();
        $this->assertEqualsCanonicalizing([
            'name' => self::NAME,
            'category' => self::CATEGORY,
            'subCategory' => self::SUB_CATEGORY,
            'type' => self::TYPE,
            'price' => self::PRICE,
            'quantity' => self::QUANTITY,
            'manufacturer' => self::MANUFACTURER,
            'merchantItemId' => self::MERCHANT_ITEM_ID,
            'other' => self::OTHER,
        ], $cartItemAsArray);
    }

    public function testCreateWithException(): void
    {
        $this->expectException(InvalidCartItemException::class);
        CartItem::create('', '', '', '', 0, 0, '', '', '');
    }

    /**
     * @throws InvalidCartItemException
     */
    public function testCreate(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $this->assertEquals(self::NAME, $cartItem->getName());
        $this->assertEquals(self::CATEGORY, $cartItem->getCategory());
        $this->assertEquals(self::SUB_CATEGORY, $cartItem->getSubCategory());
        $this->assertEquals(self::TYPE, $cartItem->getType());
        $this->assertEquals(self::PRICE, $cartItem->getPrice());
        $this->assertEquals(self::QUANTITY, $cartItem->getQuantity());
        $this->assertEquals(self::MANUFACTURER, $cartItem->getManufacturer());
        $this->assertEquals(self::MERCHANT_ITEM_ID, $cartItem->getMerchantItemId());
        $this->assertEquals(self::OTHER, $cartItem->getOther());
    }
}
