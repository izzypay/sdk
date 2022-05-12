<?php

declare(strict_types=1);

namespace Bnpl\Models;

class Other {
    private string $ip;
    private string $browser;
    private string $os;

    public function __construct(string $ip, string $browser, string $os)
    {
        $this->ip = $ip;
        $this->browser = $browser;
        $this->os = $os;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getBrowser(): string
    {
        return $this->browser;
    }

    /**
     * @param string $browser
     */
    public function setBrowser(string $browser): void
    {
        $this->browser = $browser;
    }

    /**
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * @param string $os
     */
    public function setOs(string $os): void
    {
        $this->os = $os;
    }
}
