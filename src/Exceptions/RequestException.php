<?php

namespace IzzyPay\Exceptions;

use Exception;
use Throwable;

class RequestException extends Exception
{
    private const MESSAGE = 'Error sending request: ';

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
