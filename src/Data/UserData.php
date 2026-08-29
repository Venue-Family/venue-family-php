<?php

namespace VenueFamily\Data;

class UserData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $chosenName = null,
        public readonly ?string $artistName = null,
        public readonly ?string $phone = null,
        public readonly ?string $bio = null,
        public readonly array $interests = [],
        public readonly array $organizations = [],
        public readonly bool $isStaff = false,
        public readonly ?string $profilePhotoUrl = null,
        public readonly ?string $createdAt = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            chosenName: $data['chosen_name'] ?? null,
            artistName: $data['artist_name'] ?? null,
            phone: $data['phone'] ?? null,
            bio: $data['bio'] ?? null,
            interests: $data['interests'] ?? [],
            organizations: $data['organizations'] ?? [],
            isStaff: (bool) ($data['is_staff'] ?? false),
            profilePhotoUrl: $data['profile_photo_url'] ?? null,
            createdAt: $data['created_at'] ?? null,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
