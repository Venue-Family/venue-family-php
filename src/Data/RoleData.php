<?php

namespace VenueFamily\Data;

class RoleData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $scope = 'all', // 'all' or 'own'
        public readonly array $abilities = [],
        public readonly ?string $status = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            scope: $data['scope'] ?? 'all',
            abilities: $data['abilities'] ?? [],
            status: $data['status'] ?? null,
            raw: $data
        );
    }

    public function isOwnScope(): bool
    {
        return $this->scope === 'own';
    }

    public function isAllScope(): bool
    {
        return $this->scope === 'all';
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
