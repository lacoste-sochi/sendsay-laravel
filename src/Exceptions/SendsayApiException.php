<?php

namespace Rutrue\Sendsay\Exceptions;

use Exception;

class SendsayApiException extends Exception
{
    protected array $apiErrors = [];

    public function __construct(string $message, array $apiErrors = [], int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->apiErrors = $apiErrors;
    }

    public function getApiErrors(): array
    {
        return $this->apiErrors;
    }
}