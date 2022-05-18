<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Models\Cart;
use IzzyPay\Models\CartItem;
use IzzyPay\Tests\Helpers\Traits\InvokeConstructorTrait;
use IzzyPay\Validators\CartValidator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CartValidatorTest extends TestCase
{
    use InvokeConstructorTrait;

    /**
     * @dataProvider getCartItemsProvider
     */
    public function testValidateCartItem(CartItem $cartItem, array $expected): void
    {
        $cartValidator = new CartValidator();
        $errors = $cartValidator->validateCartItem($cartItem);
        $this->assertEqualsCanonicalizing($expected, $errors);
    }

    /**
     * @dataProvider getCartsProvider
     */
    public function testValidateCart(Cart $cart, array $expected): void
    {
        $cartValidator = new CartValidator();
        $errors = $cartValidator->validateCart($cart);
        $this->assertEqualsCanonicalizing($expected, $errors);
    }

    /**
     * @throws ReflectionException
     */
    public function getCartItemsProvider(): array
    {
        $invalidCartItem1 = $this->invokeConstructor(CartItem::class, [' ', ' ', '', ' ', 0, 0, ' ', '', '']);
        $validCartItem1 = $this->invokeConstructor(CartItem::class, ['name', 'category', 'subCategory', 'type', 666.666, 666, 'merchantItemId', 'manufacturer', 'other']);
        $validCartItem2 = $this->invokeConstructor(CartItem::class, ['name', 'category', '', CartItem::TYPE_DELIVERY, 666.666, 666, 'merchantItemId', 'manufacturer', 'other']);
        return [
            [$invalidCartItem1, ['name', 'category', 'type', 'quantity', 'merchantItemId']],
            [$validCartItem1, []],
            [$validCartItem2, []],
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function getCartsProvider(): array
    {
        $invalidCartItem = $this->invokeConstructor(CartItem::class, [' ', ' ', '', ' ', 0, 0, ' ', '', '']);
        $validCartItem = $this->invokeConstructor(CartItem::class, ['name', 'category', 'subCategory', 'type', 666.666, 666, 'merchantItemId', 'manufacturer', 'other']);
        $invalidCart1 = $this->invokeConstructor(Cart::class, ['', -666.666]);
        $invalidCart2 = $this->invokeConstructor(Cart::class, ['', -666.666]);
        $invalidCart2->addItem($invalidCartItem);
        $invalidCart3 = $this->invokeConstructor(Cart::class, ['', -666.666]);
        $invalidCart3->addItem($validCartItem);
        $invalidCart4 = $this->invokeConstructor(Cart::class, ['huf', -666.666]);
        $invalidCart4->addItem($validCartItem);
        $validCart = $this->invokeConstructor(Cart::class, ['HUF', 666.666]);
        $validCart->addItem($validCartItem);
        return [
            [$invalidCart1, ['currency', 'totalValue', 'items']],
            [$invalidCart2, ['currency', 'totalValue', 'name', 'category', 'type', 'quantity', 'merchantItemId']],
            [$invalidCart3, ['currency', 'totalValue']],
            [$invalidCart3, ['currency', 'totalValue']],
            [$validCart, []],
        ];
    }
}
