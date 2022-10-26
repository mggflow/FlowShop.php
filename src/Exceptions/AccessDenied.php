<?php

namespace MGGFLOW\FlowShop\Exceptions;

use Exception;

class AccessDenied extends Exception
{
    protected $message = 'Access denied.';
}