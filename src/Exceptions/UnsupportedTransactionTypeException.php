<?php

namespace SanalPos\Exceptions;

use Exception;
use Throwable;

/**
 * Class UnsupportedTransactionTypeException
 * @package SanalPos\Exceptions
 */
class UnsupportedTransactionTypeException extends Exception
{
    /**
     * UnsupportedTransactionTypeException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Unsupported transaction type!', $code = 332, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
