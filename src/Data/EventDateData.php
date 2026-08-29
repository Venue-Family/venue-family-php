<?php

namespace VenueFamily\Data;

class EventDateData
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $date = null,
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?string $doorTime = null,
        public readonly ?string $status = null,
        public readonly bool $isSoldOut = false,
        public readonly ?int $ticketsRemaining = null,
        public readonly array $locations = [],
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            date: $data['date'] ?? null,
            startTime: $data['start_time'] ?? null,
            endTime: $data['end_time'] ?? null,
            doorTime: $data['door_time'] ?? null,
            status: $data['status'] ?? null,
            isSoldOut: (bool) ($data['is_sold_out'] ?? false),
            ticketsRemaining: isset($data['tickets_remaining']) ? (int) $data['tickets_remaining'] : null,
            locations: $data['locations'] ?? [],
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
