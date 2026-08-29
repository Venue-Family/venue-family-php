<?php

namespace VenueFamily\Data;

class FormData
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly array $fields = [],
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            fields: $data['fields'] ?? [],
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
