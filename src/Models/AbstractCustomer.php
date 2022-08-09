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
    protected ?string $companyName;
    protected string $other;

    /**
     * @param string $registered
     * @param string|null $merchantCustomerId
     * @param string|null $companyName
     * @param string $other
     */
    protected function __construct(string $registered, ?string $merchantCustomerId, ?string $companyName, string $other)
    {
        $this->registered = $registered;
        $this->merchantCustomerId = $merchantCustomerId;
        $this->companyName = $companyName;
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
     * @return AbstractCustomer
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
     * @return AbstractCustomer
     */
    public function setMerchantCustomerId(?string $merchantCustomerId): self
    {
        $this->merchantCustomerId = $merchantCustomerId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     * @return AbstractCustomer
     */
    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;
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
     * @return AbstractCustomer
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
            'companyName' => $this->companyName,
            'other' => $this->other,
        ];
    }
}
