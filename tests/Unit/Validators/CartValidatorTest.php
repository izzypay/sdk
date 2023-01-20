<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit\Validators;

use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCartItemException;
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
     * @throws InvalidCartItemException
     */
    public function testValidateCartItem(CartItem $cartItem, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $cartValidator = new CartValidator();
        $cartValidator->validateCartItem($cartItem);
        if (!$exception) {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getCartsProvider
     * @throws InvalidCartItemException
     * @throws InvalidCartException
     */
    public function testValidateCart(Cart $cart, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $cartValidator = new CartValidator();
        $cartValidator->validateCart($cart);
        if (!$exception) {
            $this->assertTrue(true);
        }
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
            [$invalidCartItem1, InvalidCartItemException::class],
            [$validCartItem1, null],
            [$validCartItem2, null],
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
            [$invalidCart1, InvalidCartException::class],
            [$invalidCart2, InvalidCartItemException::class],
            [$invalidCart3, InvalidCartException::class],
            [$invalidCart3, InvalidCartException::class],
            [$validCart, null],
        ];
    }
}
