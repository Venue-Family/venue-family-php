<?php

namespace VenueFamily\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VenueFamilyTicketsSoldOut
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly array $payload,
        public readonly int|string|null $eventId = null,
        public readonly int|string|null $eventDateId = null
    ) {}
}
