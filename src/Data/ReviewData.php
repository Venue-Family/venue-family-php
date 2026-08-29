<?php

namespace VenueFamily\Data;

class ReviewData
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $rating = null,
        public readonly ?string $authorName = null,
        public readonly ?string $authorEmail = null,
        public readonly ?string $content = null,
        public readonly ?string $status = null,
        public readonly ?string $createdAt = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            rating: isset($data['rating']) ? (int) $data['rating'] : null,
            authorName: $data['author_name'] ?? $data['name'] ?? null,
            authorEmail: $data['author_email'] ?? $data['email'] ?? null,
            content: $data['content'] ?? $data['review'] ?? null,
            status: $data['status'] ?? null,
            createdAt: $data['created_at'] ?? null,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
