<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit;

use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\IzzyPay;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Address;
use IzzyPay\Models\BasicCustomer;
use IzzyPay\Models\Cart;
use IzzyPay\Models\CartItem;
use IzzyPay\Models\DetailedCustomer;
use IzzyPay\Models\Other;
use IzzyPay\Models\Urls;
use IzzyPay\Services\RequestService;
use JsonException;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class IzzyPayTest extends TestCase
{
    private const MERCHANT_ID = 'merchantId';
    private const MERCHANT_SECRET = 'merchantSecret';
    private const BASE_URL = 'https://example.com';
    private const MERCHANT_CART_ID = 'merchantCartId';

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

    private const ZIP = '1234';
    private const CITY = 'city';
    private const STREET = 'street';
    private const HOUSE_NO = 'houseNo';
    private const ADDRESS1 = 'address1';
    private const ADDRESS2 = 'address2';
    private const ADDRESS3 = 'address3';

    private const REGISTERED = AbstractCustomer::REGISTERED_VALUE_GUEST;
    private const MERCHANT_CUSTOMER_ID = 'merchantCustomerId';
    private const SURNAME = 'surname';
    private const PHONE = '1234567890';
    private const EMAIL = 'email@example.com';

    private const IP = '192.168.1.1';
    private const BROWSER = 'Chrome';
    private const OS = 'Linux';

    private const URL = 'https://example.com';

    /**
     * @var RequestService|MockInterface $requestServiceMock
     */
    private RequestService|MockInterface $requestServiceMock;

    protected function setUp(): void
    {
        $this->requestServiceMock = Mockery::mock('overload:' . RequestService::class);
    }

    /**
     * @throws InvalidResponseException
     * @throws RequestException
     */
    public function testCred(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendHeadRequest')
            ->once()
            ->with(IzzyPay::CRED_ENDPOINT);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->cred();
        $this->assertTrue(true);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     */
    public function testInitWithException(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $basicCustomer = BasicCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $other = Other::create(self::IP, self::BROWSER, self::OS);

        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $basicCustomer->toArray(),
            'other' => $other->toArray(),
        ];
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::INIT_ENDPOINT, $body)
            ->andThrow(new RequestException('reason'));
        $this->expectException(RequestException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->init(self::MERCHANT_CART_ID, $cart, $basicCustomer, $other);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     */
    public function testInit(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $basicCustomer = BasicCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $other = Other::create(self::IP, self::BROWSER, self::OS);

        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $basicCustomer->toArray(),
            'other' => $other->toArray(),
        ];
        $result = [
            'token' => 'token',
            'available' => true,
        ];
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::INIT_ENDPOINT, $body)
            ->andReturn($result);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $response = $izzyPay->init(self::MERCHANT_CART_ID, $cart, $basicCustomer, $other);
        $this->assertEqualsCanonicalizing($result, $response);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws InvalidUrlsException
     */
    public function testStartWithException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $detailedCustomer = DetailedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::PHONE, self::EMAIL, $address, $address);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $urls = Urls::create(self::URL);

        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $detailedCustomer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::START_ENDPOINT . '/' . $token, $body)
            ->andThrow(new RequestException('reason'));
        $this->expectException(RequestException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->start($token, self::MERCHANT_CART_ID, $cart, $detailedCustomer, $other, $urls);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws InvalidUrlsException
     */
    public function testStart(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $detailedCustomer = DetailedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::PHONE, self::EMAIL, $address, $address);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $urls = Urls::create(self::URL);

        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $detailedCustomer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];
        $result = [
            'token' => 'token',
            'available' => true,
        ];
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::START_ENDPOINT . '/' . $token, $body)
            ->andReturn($result);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $response = $izzyPay->start($token, self::MERCHANT_CART_ID, $cart, $detailedCustomer, $other, $urls);
        $this->assertEqualsCanonicalizing($result, $response);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
