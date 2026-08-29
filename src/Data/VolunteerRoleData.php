<?php

namespace VenueFamily\Data;

class VolunteerRoleData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $date = null,
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?int $spotsNeeded = null,
        public readonly ?int $spotsFilled = null,
        public readonly ?string $status = null,
        public readonly ?string $organizationSlug = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: $data['name'] ?? $data['title'] ?? '',
            description: $data['description'] ?? null,
            date: $data['date'] ?? null,
            startTime: $data['start_time'] ?? null,
            endTime: $data['end_time'] ?? null,
            spotsNeeded: isset($data['spots_needed']) ? (int) $data['spots_needed'] : null,
            spotsFilled: isset($data['spots_filled']) ? (int) $data['spots_filled'] : null,
            status: $data['status'] ?? null,
            organizationSlug: $data['organization_slug'] ?? null,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
