<?php

namespace Mamba\Latipay\Exceptions;

class BusinessException extends Exception
{
    public function __construct($message, $raw = [])
    {
        parent::__construct('ERROR_BUSINESS: '.$message, $raw, self::ERROR_BUSINESS);
    }
}
