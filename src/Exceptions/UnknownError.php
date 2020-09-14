<?php

namespace Ankapix\SanalPos\Exceptions;

use Exception;
use Throwable;

/**
 * Class UnknownError
 * @package Ankapix\SanalPos\Exceptions
 */
class UnknownError extends Exception
{
    /**
     * UnknownError constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Something went wrong.', $code = 334, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
