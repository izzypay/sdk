<?php

namespace IzzyPay\Exceptions;

use Exception;
use Throwable;

class InvalidUrlsException extends Exception
{
    private const MESSAGE = 'Invalid fields in urls: ';

    /**
     * @param array $invalidFields
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(array $invalidFields, int $code = 0, Throwable $previous = null)
    {
        $message = self::MESSAGE . implode(', ', $invalidFields);
        parent::__construct($message, $code, $previous);
    }
}
