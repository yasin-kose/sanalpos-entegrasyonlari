<?php

namespace Ankapix\SanalPos\Exceptions;

use Exception;
use Throwable;

/**
 * Class BankNotFoundException
 * @package Ankapix\SanalPos\Exceptions
 */
class BankNotFoundException extends Exception
{
    /**
     * BankNotFoundException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Bank not found!', $code = 330, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
