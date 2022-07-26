<?php

declare(strict_types=1);

namespace IzzyPay\Models;

abstract class AbstractCustomer
{
    public const REGISTERED_VALUE_GUEST = 'guest';
    public const REGISTERED_VALUE_MERCHANT = 'merchant';
    public const REGISTERED_VALUE_3RDPARTY = '3rdparty';
    public const ALLOWED_REGISTERED_VALUES = [self::REGISTERED_VALUE_GUEST, self::REGISTERED_VALUE_MERCHANT, self::REGISTERED_VALUE_3RDPARTY];

    protected string $registered;
    protected ?string $merchantCustomerId;
    protected string $other;

    /**
     * @param string $registered
     * @param string|null $merchantCustomerId
     * @param string $other
     */
    protected function __construct(string $registered, ?string $merchantCustomerId, string $other)
    {
        $this->registered = $registered;
        $this->merchantCustomerId = $merchantCustomerId;
        $this->other = $other;
    }

    /**
     * @return string
     */
    public function getRegistered(): string
    {
        return $this->registered;
    }

    /**
     * @param string $registered
     * @return LimitedCustomer
     */
    public function setRegistered(string $registered): self
    {
        $this->registered = $registered;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMerchantCustomerId(): ?string
    {
        return $this->merchantCustomerId;
    }

    /**
     * @param string|null $merchantCustomerId
     * @return LimitedCustomer
     */
    public function setMerchantCustomerId(?string $merchantCustomerId): self
    {
        $this->merchantCustomerId = $merchantCustomerId;
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
     * @return LimitedCustomer
     */
    public function setOther(string $other): self
    {
        $this->other = $other;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'registered' => $this->registered,
            'merchantCustomerId' => $this->merchantCustomerId,
            'other' => $this->other,
        ];
    }
}
