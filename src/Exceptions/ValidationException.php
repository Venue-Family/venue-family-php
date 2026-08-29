<?php

namespace VenueFamily\Exceptions;

class ValidationException extends VenueFamilyException
{
    public function __construct(
        string $message = 'Validation failed',
        private readonly array $errors = [],
        ?array $responseBody = null
    ) {
        parent::__construct($message, 422, $responseBody);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
