<?php

namespace IzzyPay\Exceptions;

use Exception;
use Throwable;

class InvalidResponseException extends Exception
{
    private const MESSAGE = 'Invalid response: ';

    /**
     * @param array $errors
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(array $errors, int $code = 0, Throwable $previous = null)
    {
        $message = self::MESSAGE . implode(',', $errors);
        parent::__construct($message, $code, $previous);
    }
}
