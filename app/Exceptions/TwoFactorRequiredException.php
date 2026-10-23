<?php

namespace App\Exceptions;

use Exception;

class TwoFactorRequiredException extends Exception
{
    protected $message = 'Two-factor authentication required.';
}
