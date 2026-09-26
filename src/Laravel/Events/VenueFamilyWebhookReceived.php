<?php

namespace VenueFamily\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VenueFamilyWebhookReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly array $payload,
        public readonly string $eventType
    ) {}
}
