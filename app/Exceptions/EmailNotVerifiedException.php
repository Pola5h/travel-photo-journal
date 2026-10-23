<?php

namespace App\Exceptions;

use Exception;

class EmailNotVerifiedException extends Exception
{
    protected $message = 'Your email address is not verified.';
}
