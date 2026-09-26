<?php

namespace VenueFamily\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VenueFamilyEventUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly array $payload,
        public readonly int|string|null $eventId = null,
        public readonly ?string $eventSlug = null
    ) {}
}
