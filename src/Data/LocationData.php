<?php

namespace VenueFamily\Data;

class LocationData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $stub = null,
        public readonly ?int $capacity = null,
        public readonly ?string $pricePerHour = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $zip = null,
        public readonly array $spaces = [],
        public readonly array $features = [],
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: $data['name'] ?? '',
            stub: $data['stub'] ?? null,
            capacity: isset($data['capacity']) ? (int) $data['capacity'] : null,
            pricePerHour: isset($data['price_per_hour']) ? (string) $data['price_per_hour'] : null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zip: $data['zip'] ?? null,
            spaces: $data['spaces'] ?? [],
            features: $data['features'] ?? [],
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
