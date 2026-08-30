<?php

namespace VenueFamily\Data;

class PhaseData
{
    public function __construct(
        public readonly ?string $key,
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?string $startTimeFormatted = null,
        public readonly ?string $endTimeFormatted = null,
        public readonly int $sort = 0,
        public readonly array $locations = [],
        public readonly ?int $capacity = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            startTime: $data['start_time'] ?? null,
            endTime: $data['end_time'] ?? null,
            startTimeFormatted: $data['start_time_formatted'] ?? null,
            endTimeFormatted: $data['end_time_formatted'] ?? null,
            sort: (int) ($data['sort'] ?? 0),
            locations: $data['locations'] ?? [],
            capacity: isset($data['capacity']) ? (int) $data['capacity'] : null,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
