<?php

namespace App\Exceptions;

use Exception;

class TwoFactorAlreadyEnabledException extends Exception
{
    protected $message = 'Two-factor authentication is already enabled.';
}
