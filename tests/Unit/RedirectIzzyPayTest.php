<?php

declare(strict_types=1);

namespace IzzyPay\Tests\Unit;

use DateTimeImmutable;
use IzzyPay\Exceptions\AuthenticationException;
use IzzyPay\Exceptions\InvalidAddressException;
use IzzyPay\Exceptions\InvalidCartException;
use IzzyPay\Exceptions\InvalidCartItemException;
use IzzyPay\Exceptions\InvalidCustomerException;
use IzzyPay\Exceptions\InvalidOtherException;
use IzzyPay\Exceptions\InvalidResponseException;
use IzzyPay\Exceptions\InvalidReturnDataException;
use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Exceptions\PaymentServiceUnavailableException;
use IzzyPay\Exceptions\RequestException;
use IzzyPay\Models\AbstractCustomer;
use IzzyPay\Models\Address;
use IzzyPay\Models\CreateOther;
use IzzyPay\Models\LimitedCustomer;
use IzzyPay\Models\Cart;
use IzzyPay\Models\CartItem;
use IzzyPay\Models\Customer;
use IzzyPay\Models\Other;
use IzzyPay\Models\RedirectUrls;
use IzzyPay\Models\Response\CreateResponse;
use IzzyPay\Models\Response\RedirectInitResponse;
use IzzyPay\RedirectIzzyPay;
use IzzyPay\Services\HmacService;
use IzzyPay\Services\RequestService;
use IzzyPay\Validators\ResponseValidator;
use IzzyPay\Validators\ReturnValidator;
use JsonException;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

/**
 * @runTestsInSeparateProcesses
 */
class RedirectIzzyPayTest extends TestCase
{
    private const MERCHANT_ID = 'merchantId';
    private const MERCHANT_SECRET = 'merchantSecret';
    private const BASE_URL = 'https://test.izzypay.hu';
    private const MERCHANT_CART_ID = 'merchantCartId';

    private const CURRENCY = 'HUF';
    private const TOTAL_VALUE = 46000.00;
    private const NAME = 'name';
    private const CATEGORY = 'category';
    private const SUB_CATEGORY = 'subCategory';
    private const TYPE = 'product';
    private const PRICE = 666.66;
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

    private const ACCEPTED_URL = 'https://webshop.url/accepted';
    private const REJECTED_URL = 'https://webshop.url/rejected';
    private const CANCELLED_URL = 'https://webshop.url/cancelled';
    private const IPN_URL = 'https://webshop.url/ipn';
    private const CHECKOUT_URL = 'https://webshop.url/checkout';

    private MockInterface $responseValidatorMock;
    private MockInterface $requestServiceMock;
    private MockInterface $returnValidatorMock;

    protected function setUp(): void
    {
        Mockery::mock('overload:' . HmacService::class);
        $this->responseValidatorMock = Mockery::mock('overload:' . ResponseValidator::class);
        $this->requestServiceMock = Mockery::mock('overload:' . RequestService::class);
        $this->returnValidatorMock = Mockery::mock('overload:' . ReturnValidator::class);
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
            ->with(RedirectIzzyPay::CRED_ENDPOINT)
            ->andThrow(new RequestException(''));
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
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
            ->with(RedirectIzzyPay::CRED_ENDPOINT)
            ->andThrow(new AuthenticationException(''));
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
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
            ->with(RedirectIzzyPay::CRED_ENDPOINT);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
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
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER);
        $other = Other::create(self::BROWSER);
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
            ->with(RedirectIzzyPay::INIT_ENDPOINT, $body)
            ->andThrow(new RequestException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateRedirectInitResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyInitAvailability');

        $this->expectException(RequestException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
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
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER);
        $other = Other::create(self::BROWSER);
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
            ->with(RedirectIzzyPay::INIT_ENDPOINT, $body)
            ->andThrow(new AuthenticationException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateRedirectInitResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyInitAvailability');

        $this->expectException(AuthenticationException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
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
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER);
        $other = Other::create(self::BROWSER);
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
            ->with(RedirectIzzyPay::INIT_ENDPOINT, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateRedirectInitResponse')
            ->once()
            ->with($response)
            ->andThrow(new InvalidResponseException(['merchantId', 'available']));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyInitAvailability');

        $this->expectException(InvalidResponseException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
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
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER);
        $other = Other::create(self::BROWSER);
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
            ->with(RedirectIzzyPay::INIT_ENDPOINT, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateRedirectInitResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyInitAvailability')
            ->once()
            ->andThrow(new PaymentServiceUnavailableException(['token']));

        $this->expectException(PaymentServiceUnavailableException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
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
        $limitedCustomer = LimitedCustomer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::COMPANY_NAME, self::OTHER);
        $other = Other::create(self::BROWSER);
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
            'available' => true,
        ];
        $initResponse = new RedirectInitResponse($response['token'], $response['merchantId'], $response['merchantCartId']);
        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(RedirectIzzyPay::INIT_ENDPOINT, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateRedirectInitResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyInitAvailability')
            ->once()
            ->andReturn();

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $result = $izzyPay->init(self::MERCHANT_CART_ID, $cart, $limitedCustomer, $other);
        $this->assertEquals($initResponse, $result);
    }

    // </editor-fold>

    // <editor-fold desc=create()>

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
     * @throws InvalidAddressException
     */
    public function testCreateWithRequestException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME,self::PHONE, self::EMAIL, $address, $address);
        $other = CreateOther::create(self::IP, self::BROWSER);
        $urls = RedirectUrls::create(
            self::ACCEPTED_URL,
            self::REJECTED_URL,
            self::CANCELLED_URL,
            self::IPN_URL,
            self::CHECKOUT_URL
        );
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
            ->with(RedirectIzzyPay::CREATE_ENDPOINT . '/' . $token, $body)
            ->andThrow(new RequestException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateCreateResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyCreateAvailability');

        $this->expectException(RequestException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->create($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
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
     * @throws InvalidAddressException
     */
    public function testCreateWithAuthenticationException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = CreateOther::create(self::IP, self::BROWSER);
        $urls = RedirectUrls::create(
            self::ACCEPTED_URL,
            self::REJECTED_URL,
            self::CANCELLED_URL,
            self::IPN_URL,
            self::CHECKOUT_URL
        );
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
            ->with(RedirectIzzyPay::CREATE_ENDPOINT . '/' . $token, $body)
            ->andThrow(new AuthenticationException('reason'));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('validateCreateResponse');
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyCreateAvailability');

        $this->expectException(AuthenticationException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->create($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
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
     * @throws InvalidAddressException
     */
    public function testCreateWithInvalidResponseException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = CreateOther::create(self::IP, self::BROWSER);
        $urls = RedirectUrls::create(
            self::ACCEPTED_URL,
            self::REJECTED_URL,
            self::CANCELLED_URL,
            self::IPN_URL,
            self::CHECKOUT_URL
        );
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
            ->with(RedirectIzzyPay::CREATE_ENDPOINT . '/' . $token, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateCreateResponse')
            ->once()
            ->with($response)
            ->andThrow(new InvalidResponseException(['merchantId', 'available']));
        $this->responseValidatorMock
            ->shouldNotHaveReceived('verifyCreateAvailability');

        $this->expectException(InvalidResponseException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->create($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
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
     * @throws InvalidAddressException
     */
    public function testCreateWithPaymentServiceUnavailableException(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = CreateOther::create(self::IP, self::BROWSER);
        $urls = RedirectUrls::create(
            self::ACCEPTED_URL,
            self::REJECTED_URL,
            self::CANCELLED_URL,
            self::IPN_URL,
            self::CHECKOUT_URL
        );
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
            ->with(RedirectIzzyPay::CREATE_ENDPOINT . '/' . $token, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateCreateResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyCreateAvailability')
            ->once()
            ->andThrow(new PaymentServiceUnavailableException(['token']));

        $this->expectException(PaymentServiceUnavailableException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->create($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
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
     * @throws InvalidAddressException
     */
    public function testCreate(): void
    {
        $token = 'token';
        $cartItem = CartItem::create(self::NAME, self::CATEGORY, self::SUB_CATEGORY, self::TYPE, self::PRICE, self::QUANTITY, self::MANUFACTURER, self::MERCHANT_ITEM_ID, self::OTHER);
        $cart = Cart::create(self::CURRENCY, self::TOTAL_VALUE, [$cartItem]);
        $address = Address::create(self::ZIP, self::CITY, self::STREET, self::HOUSE_NO, self::ADDRESS1, self::ADDRESS2, self::ADDRESS3);
        $customer = Customer::create(self::REGISTERED, self::MERCHANT_CUSTOMER_ID, self::OTHER, self::NAME, self::SURNAME, self::COMPANY_NAME, self::PHONE, self::EMAIL, $address, $address);
        $other = CreateOther::create(self::IP, self::BROWSER);
        $urls = RedirectUrls::create(
            self::ACCEPTED_URL,
            self::REJECTED_URL,
            self::CANCELLED_URL,
            self::IPN_URL,
            self::CHECKOUT_URL
        );
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
            'redirectUrl' => 'https://test.izzpay.hu/redirect',
        ];
        $createResponse = new CreateResponse($response['token'], $response['merchantId'], $response['merchantCartId'], $response['redirectUrl']);

        $this->requestServiceMock
            ->shouldReceive('sendPostRequest')
            ->once()
            ->with(RedirectIzzyPay::CREATE_ENDPOINT . '/' . $token, $body)
            ->andReturn($response);
        $this->responseValidatorMock
            ->shouldReceive('validateCreateResponse')
            ->once()
            ->with($response);
        $this->responseValidatorMock
            ->shouldReceive('verifyCreateAvailability')
            ->once();

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $result = $izzyPay->create($token, self::MERCHANT_CART_ID, $cart, $customer, $other, $urls);
        $this->assertEquals($createResponse, $result);
    }

    // </editor-fold>

    // <editor-fold desc=delivery()>

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testDeliveryCartWithRequestException(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(RedirectIzzyPay::DELIVERY_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID)
            ->andThrow(new RequestException('reason'));

        $this->expectException(RequestException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->deliveryCart(self::MERCHANT_CART_ID);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testDeliveryCartWithAuthenticationException(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(RedirectIzzyPay::DELIVERY_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID)
            ->andThrow(new AuthenticationException('reason'));

        $this->expectException(AuthenticationException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->deliveryCart(self::MERCHANT_CART_ID);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testDelivery(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(RedirectIzzyPay::DELIVERY_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID)
            ->andReturn([]);

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->deliveryCart(self::MERCHANT_CART_ID);
        $this->assertTrue(true);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testDeliveryItemWithRequestException(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(RedirectIzzyPay::DELIVERY_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID . '/' . self::MERCHANT_ITEM_ID)
            ->andThrow(new RequestException('reason'));

        $this->expectException(RequestException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->deliveryItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testDeliveryItemWithAuthenticationException(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(RedirectIzzyPay::DELIVERY_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID . '/' . self::MERCHANT_ITEM_ID)
            ->andThrow(new AuthenticationException('reason'));

        $this->expectException(AuthenticationException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->deliveryItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     */
    public function testDeliveryItem(): void
    {
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(RedirectIzzyPay::DELIVERY_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID . '/' . self::MERCHANT_ITEM_ID)
            ->andReturn([]);

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->deliveryItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID);
        $this->assertTrue(true);
    }

    // </editor-fold>

    // <editor-fold desc=return()>

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnCartWithRequestException(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(
                RedirectIzzyPay::RETURN_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID,
                ['returnDate' => $returnDate->format(DateTimeImmutable::ISO8601)]
            )
            ->andThrow(new RequestException('reason'));

        $this->expectException(RequestException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnCart(self::MERCHANT_CART_ID, $returnDate);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnCartWithAuthenticationException(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(
                RedirectIzzyPay::RETURN_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID,
                ['returnDate' => $returnDate->format(DateTimeImmutable::ISO8601)]
            )
            ->andThrow(new AuthenticationException('reason'));

        $this->expectException(AuthenticationException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnCart(self::MERCHANT_CART_ID, $returnDate);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnCart(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(
                RedirectIzzyPay::RETURN_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID,
                ['returnDate' => $returnDate->format(DateTimeImmutable::ISO8601)]
            )
            ->andReturn([]);

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnCart(self::MERCHANT_CART_ID, $returnDate);
        $this->assertTrue(true);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnItemWithInvalidReturnDataException(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $reducedValue = -100.2;
        $this->returnValidatorMock
            ->shouldReceive('validate')
            ->once()
            ->with($reducedValue)
            ->andThrow(new InvalidReturnDataException(['reducedValue']));
        $this->requestServiceMock
            ->shouldNotHaveReceived('sendPutRequest');

        $this->expectException(InvalidReturnDataException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID, $returnDate, $reducedValue);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnItemWithRequestException(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $reducedValue = 100.2;
        $this->returnValidatorMock
            ->shouldReceive('validate')
            ->once()
            ->with($reducedValue);
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(
                RedirectIzzyPay::RETURN_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID . '/' . self::MERCHANT_ITEM_ID,
                ['returnDate' => $returnDate->format(DateTimeImmutable::ISO8601), 'reducedValue' => $reducedValue]
            )
            ->andThrow(new RequestException('reason'));

        $this->expectException(RequestException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID, $returnDate, $reducedValue);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnItemWithAuthenticationException(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $reducedValue = 100.2;
        $this->returnValidatorMock
            ->shouldReceive('validate')
            ->once()
            ->with($reducedValue);
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(
                RedirectIzzyPay::RETURN_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID . '/' . self::MERCHANT_ITEM_ID,
                ['returnDate' => $returnDate->format(DateTimeImmutable::ISO8601), 'reducedValue' => $reducedValue]
            )
            ->andThrow(new AuthenticationException('reason'));

        $this->expectException(AuthenticationException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID, $returnDate, $reducedValue);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnItemWithoutReducedValue(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $this->returnValidatorMock
            ->shouldReceive('validate')
            ->once()
            ->with(null);
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(
                RedirectIzzyPay::RETURN_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID . '/' . self::MERCHANT_ITEM_ID,
                ['returnDate' => $returnDate->format(DateTimeImmutable::ISO8601)]
            )
            ->andReturn([]);

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID, $returnDate);
        $this->assertTrue(true);
    }

    /**
     * @throws RequestException
     * @throws JsonException
     * @throws AuthenticationException
     * @throws InvalidReturnDataException
     */
    public function testReturnItemWithReducedValue(): void
    {
        $returnDate = new DateTimeImmutable('2022-04-04T12:34:56+0010');
        $reducedValue = 100.2;
        $this->returnValidatorMock
            ->shouldReceive('validate')
            ->once()
            ->with($reducedValue);
        $this->requestServiceMock
            ->shouldReceive('sendPutRequest')
            ->once()
            ->with(
                RedirectIzzyPay::RETURN_ENDPOINT . '/' . self::MERCHANT_ID . '/' . self::MERCHANT_CART_ID . '/' . self::MERCHANT_ITEM_ID,
                ['returnDate' => $returnDate->format(DateTimeImmutable::ISO8601), 'reducedValue' => $reducedValue]
            )
            ->andReturn([]);

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->returnItem(self::MERCHANT_CART_ID, self::MERCHANT_ITEM_ID, $returnDate, $reducedValue);
        $this->assertTrue(true);
    }

    // </editor-fold>

    // <editor-fold desc=validateAuthentication()>

    public function testValidateAuthenticationWithInvalidAuthorizationHeader(): void
    {
        $this->requestServiceMock
            ->shouldReceive('validateAuthentication')
            ->once()
            ->with('content', 'Bearer signature')
            ->andThrow(new AuthenticationException('Invalid signature'));

        $this->expectException(AuthenticationException::class);
        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->validateAuthentication('content', 'Bearer signature');
    }

    /**
     * @throws AuthenticationException
     */
    public function validateAuthentication(): void
    {
        $this->responseValidatorMock
            ->shouldReceive('validateAuthentication')
            ->once()
            ->with('content', 'Bearer signature');

        $izzyPay = new RedirectIzzyPay(self::MERCHANT_ID, self::MERCHANT_SECRET, self::BASE_URL);
        $izzyPay->validateAuthentication('content', 'Bearer signature');

        $this->assertTrue(true);
    }

    // </editor-fold>

    public function tearDown(): void
    {
        Mockery::close();
    }
}
