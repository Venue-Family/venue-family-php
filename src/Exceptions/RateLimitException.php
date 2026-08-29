<?php

namespace VenueFamily\Exceptions;

class RateLimitException extends VenueFamilyException
{
    public function __construct(
        string $message = 'Too Many Requests',
        private readonly ?int $retryAfterSeconds = null,
        ?array $responseBody = null
    ) {
        parent::__construct($message, 429, $responseBody);
    }

    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }
}
