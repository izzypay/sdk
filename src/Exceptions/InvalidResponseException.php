<?php

namespace Bnpl\Exception;

use Exception;
use Throwable;

class InvalidResponseException extends Exception
{
    private const MESSAGE = 'Invalid respopnse: ';

    /**
     * @param string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE . $message, $code, $previous);
    }
}
