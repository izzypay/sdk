<?php

namespace IzzyPay\Exceptions;

use Exception;
use JsonException;
use Throwable;

class PaymentServiceUnavailableException extends Exception
{
    private const MESSAGE = 'Payment service unavailable: ';

    /**
     * @param array $errors
     * @param int $code
     * @param ?Throwable $previous
     * @throws JsonException
     */
    public function __construct(array $errors, int $code = 0, Throwable $previous = null)
    {
        $message = self::MESSAGE . json_encode($errors, JSON_THROW_ON_ERROR);
        parent::__construct($message, $code, $previous);
    }
}
