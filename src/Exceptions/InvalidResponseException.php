<?php

namespace IzzyPay\Exceptions;

use Exception;
use Throwable;

class InvalidResponseException extends Exception
{
    private const MESSAGE = 'Invalid response: ';

    /**
     * @param string $reason
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(string $reason, int $code = 0, Throwable $previous = null)
    {
        $message = self::MESSAGE . $reason;
        parent::__construct($message, $code, $previous);
    }
}
