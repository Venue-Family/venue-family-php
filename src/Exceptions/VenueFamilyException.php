<?php

namespace VenueFamily\Exceptions;

use Exception;

class VenueFamilyException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        private readonly ?array $responseBody = null
    ) {
        parent::__construct($message, $code);
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
