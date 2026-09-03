<?php

namespace VenueFamily\Data;

class MediaSlotData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slot,
        public readonly int $sortOrder = 0,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array|string $data): self
    {
        if (is_string($data)) {
            return new self(
                name: $data,
                slot: $data,
                sortOrder: 0,
                raw: ['name' => $data, 'slot' => $data, 'sort_order' => 0]
            );
        }

        $slot = (string) ($data['slot'] ?? $data['name'] ?? '');
        $name = (string) ($data['name'] ?? $slot);
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        return new self(
            name: $name,
            slot: $slot,
            sortOrder: $sortOrder,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
