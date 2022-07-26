<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit;

use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Exceptions\PaymentServiceUnavailableException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\IzzyPay;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Address;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Cart;
use IzzyPay\Models\CartItem;
use IzzyPay\Models\Customer;
use IzzyPay\Models\Other;
use IzzyPay\Models\Response\InitResponse;
use IzzyPay\Models\Response\StartResponse;
use IzzyPay\Models\Urls;
use IzzyPay\Services\HmacService;
use IzzyPay\Services\RequestService;
use IzzyPay\Validators\ResponseValidator;
use JsonException;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

/**
 * @runTestsInSeparateProcesses
 */
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
    private const COMPANY_NAME = 'company name';
    private const EMAIL = 'email@example.com';

    private const IP = '192.168.1.1';
    private const BROWSER = 'Chrome';
    private const OS = 'Linux';

    private const URL = 'https://example.com';

    private ResponseValidator|MockInterface $responseValidatorMock;
    private RequestService|MockInterface $requestServiceMock;

    protected function setUp(): void
    {
        Mockery::mock('overload:' . HmacService::class);
        $this->responseValidatorMock = Mockery::mock('overload:' . ResponseValidator::class);
        $this->requestServiceMock = Mockery::mock('overload:' . RequestService::class);
    }

    // <editor-fold desc=cred()>

    /**
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function testCredWithRequestException(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendHeadRequest')
            ->once()
            ->with(IzzyPay::CRED_ENDPOINT)
            ->andThrow(new RequestException(''));
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $this->expectException(RequestException::class);
        $izzyPay->cred();
    }

    /**
     * @throws RequestException
     * @throws AuthenticationException
     */
    public function testCredWithAuthenticationException(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendHeadRequest')
            ->once()
            ->with(IzzyPay::CRED_ENDPOINT)
            ->andThrow(new AuthenticationException(''));
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $this->expectException(AuthenticationException::class);
        $izzyPay->cred();
    }

    /**
     * @throws RequestException
     * @throws AuthenticationException
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

    // </editor-fold>

    // <editor-fold desc=init()>

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidCartItemException
     */
    public function testInitWithRequestException(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $limitedCustomer->toArray(),
            'other' => $other->toArray(),
        ];

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::INIT_ENDPOINT, $body)
            ->andThrow(new RequestException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateInitResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyInitAvailability');

        $this->expectException(RequestException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->init(self::MERCHANT_CART_ID, $cart, $limitedCustomer, $other);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidCartItemException
     */
    public function testInitWithAuthenticationException(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $limitedCustomer->toArray(),
            'other' => $other->toArray(),
        ];

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::INIT_ENDPOINT, $body)
            ->andThrow(new AuthenticationException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateInitResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyInitAvailability');

        $this->expectException(AuthenticationException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->init(self::MERCHANT_CART_ID, $cart, $limitedCustomer, $other);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidCartItemException
     */
    public function testInitWithInvalidResponseException(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $limitedCustomer->toArray(),
            'other' => $other->toArray(),
        ];
        $response = [
            'token' => 'token',
        ];
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::INIT_ENDPOINT, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateInitResponse')
            ->once()
            ->with($response)
            ->andThrow(new InvalidResponseException(['merchantId', 'available']));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyInitAvailability');

        $this->expectException(InvalidResponseException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->init(self::MERCHANT_CART_ID, $cart, $limitedCustomer, $other);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidCartItemException
     */
    public function testInitWithPaymentServiceUnavailableException(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $limitedCustomer->toArray(),
            'other' => $other->toArray(),
        ];
        $response = [
            'token' => 'token',
        ];
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::INIT_ENDPOINT, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateInitResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyInitAvailability')
            ->once()
            ->andThrow(new PaymentServiceUnavailableException(['token']));

        $this->expectException(PaymentServiceUnavailableException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->init(self::MERCHANT_CART_ID, $cart, $limitedCustomer, $other);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidCartItemException
     */
    public function testInit(): void
    {
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $limitedCustomer->toArray(),
            'other' => $other->toArray(),
        ];
        $response = [
            'token' => 'token',
            'merchantId' => 'merchant id',
            'merchantCartId' => 'merchant cart id',
            'jsUrl' => 'https://www.example.com',
            'available' => true,
        ];
        $initResponse = new InitResponse($response['token'], $response['merchantId'], $response['merchantCartId'], $response['jsUrl']);
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::INIT_ENDPOINT, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateInitResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyInitAvailability')
            ->once()
            ->andReturn();

        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $result = $izzyPay->init(self::MERCHANT_CART_ID, $cart, $limitedCustomer, $other);
        $this->assertEquals($initResponse, $result);
    }

    // </editor-fold>

    // <editor-fold desc=start()>

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidUrlsException
     * @throws InvalidCartItemException
     */
    public function testStartWithRequestException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME,self::PHONE, self::EMAIL, $address, $address);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $urls = Urls::create(self::URL);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::START_ENDPOINT . '/' . $token, $body)
            ->andThrow(new RequestException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateStartResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyStartAvailability');

        $this->expectException(RequestException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->start($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidUrlsException
     * @throws InvalidCartItemException
     */
    public function testStartWithAuthenticationException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $urls = Urls::create(self::URL);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::START_ENDPOINT . '/' . $token, $body)
            ->andThrow(new AuthenticationException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateStartResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyStartAvailability');

        $this->expectException(AuthenticationException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->start($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidUrlsException
     * @throws InvalidCartItemException
     */
    public function testStartWithInvalidResponseException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $urls = Urls::create(self::URL);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];
        $response = [
            'token' => $token,
        ];

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::START_ENDPOINT . '/' . $token, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateStartResponse')
            ->once()
            ->with($response)
            ->andThrow(new InvalidResponseException(['merchantId', 'available']));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyStartAvailability');

        $this->expectException(InvalidResponseException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->start($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidUrlsException
     * @throws InvalidCartItemException
     */
    public function testStartWithPaymentServiceUnavailableException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $urls = Urls::create(self::URL);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];
        $response = [
            'token' => $token,
        ];

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::START_ENDPOINT . '/' . $token, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateStartResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyStartAvailability')
            ->once()
            ->andThrow(new PaymentServiceUnavailableException(['token']));

        $this->expectException(PaymentServiceUnavailableException::class);
        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->start($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
    }

    /**
     * @throws RequestException
     * @throws InvalidResponseException
     * @throws JsonException
     * @throws InvalidCartException
     * @throws InvalidOtherException
     * @throws InvalidCustomerException
     * @throws AuthenticationException
     * @throws PaymentServiceUnavailableException
     * @throws InvalidUrlsException
     * @throws InvalidCartItemException
     */
    public function testStart(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = Other::create(self::IP, self::BROWSER, self::OS);
        $urls = Urls::create(self::URL);
        $body = [
            'merchantId' => self::MERCHANT_ID,
            'merchantCartId' => self::MERCHANT_CART_ID,
            'cart' => $cart->toArray(),
            'customer' => $customer->toArray(),
            'other' => $other->toArray(),
            'urls' => $urls->toArray(),
        ];
        $response = [
            'token' => $token,
            'merchantId' => 'merchant id',
            'merchantCartId' => 'merchant cart id',
        ];
        $startResponse = new StartResponse($response['token'], $response['merchantId'], $response['merchantCartId']);

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(IzzyPay::START_ENDPOINT . '/' . $token, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateStartResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyStartAvailability')
            ->once();

        $izzyPay = new IzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $result = $izzyPay->start($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
        $this->assertEquals($startResponse, $result);
    }

    // </editor-fold>

    public function tearDown(): void
    {
        Mockery::close();
    }
}