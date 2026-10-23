<?php

namespace App\Exceptions;

use Exception;

class InvalidVerificationTokenException extends Exception
{
    protected $message = 'This verification link is invalid or has expired.';
}
