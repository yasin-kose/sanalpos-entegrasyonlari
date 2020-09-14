<?php

namespace Ankapix\SanalPos\Exceptions;

use Exception;
use Throwable;

/**
 * Class UnsupportedPaymentModelException
 * @package Ankapix\SanalPos\Exceptions
 */
class UnsupportedPaymentModelException extends Exception
{
    /**
     * UnsupportedPaymentModelException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Unsupported payment model!', $code = 332, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
