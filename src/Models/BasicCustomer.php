<?php

declare(strict_types=1);

namespace Bnpl\Models;

class BasicCustomer {
    private string $registered;
    private string $merchantCustomerId;
    private string $other;

    public function __construct(string $registered, string $merchantCustomerId, string $other)
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
     */
    public function setRegistered(string $registered): void
    {
        $this->registered = $registered;
    }

    /**
     * @return string
     */
    public function getMerchantCustomerId(): string
    {
        return $this->merchantCustomerId;
    }

    /**
     * @param string $merchantCustomerId
     */
    public function setMerchantCustomerId(string $merchantCustomerId): void
    {
        $this->merchantCustomerId = $merchantCustomerId;
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
     */
    public function setOther(string $other): void
    {
        $this->other = $other;
    }
}
