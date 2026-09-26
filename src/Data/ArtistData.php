<?php

namespace VenueFamily\Data;

class ArtistData
{
    public readonly string $name;

    public function __construct(
        public readonly int $id,
        public readonly string $artistName,
        public readonly ?string $bio = null,
        public readonly ?string $profileImage = null,
        public readonly ?string $location = null,
        public readonly ?int $travelDistance = null,
        public readonly ?string $website = null,
        public readonly array $interests = [],
        public readonly array $portfolioImages = [],
        public readonly array $socialMedia = [],
        public readonly array $links = [],
        public readonly array $raw = []
    ) {
        $this->name = $this->artistName;
    }

    public static function fromArray(array $data): self
    {
        $socialMedia = [];
        if (! empty($data['social_media'])) {
            $socialMedia = is_array($data['social_media'])
                ? $data['social_media']
                : (array) $data['social_media'];
        }

        $artistName = (string) ($data['artist_name'] ?? $data['name'] ?? '');

        return new self(
            id: (int) ($data['id'] ?? 0),
            artistName: $artistName,
            bio: $data['bio'] ?? null,
            profileImage: $data['profile_image'] ?? $data['profile_photo_url'] ?? null,
            location: $data['location'] ?? null,
            travelDistance: isset($data['travel_distance']) ? (int) $data['travel_distance'] : null,
            website: $data['website'] ?? null,
            interests: $data['interests'] ?? [],
            portfolioImages: $data['portfolio_images'] ?? [],
            socialMedia: $socialMedia,
            links: $data['links'] ?? [],
            raw: $data
        );
    }

    public function cleanBio(): string
    {
        $bio = $this->bio;
        if ($bio === null || trim($bio) === '') {
            return '';
        }

        $bio = preg_replace('/\\\\<([a-zA-Z\/][^>]*?)(\\\\>|>)/', '<$1>', $bio);
        $bio = str_replace(['\<', '\>'], ['<', '>'], $bio);
        $bio = preg_replace('/<\/?(font|o:p)[^>]*>/i', '', $bio);
        $bio = preg_replace('/\s*style=("|\')[^"\']*("|\')/i', '', $bio);
        $bio = preg_replace('/<\/?span[^>]*>/i', '', $bio);

        return trim($bio);
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'name', 'artist_name' => $this->artistName,
            'profile_image' => $this->profileImage,
            'travel_distance' => $this->travelDistance,
            'portfolio_images' => $this->portfolioImages,
            'social_media' => $this->socialMedia,
            default => $this->raw[$name] ?? null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'name', 'artist_name' => true,
            'profile_image' => $this->profileImage !== null,
            'travel_distance' => $this->travelDistance !== null,
            'portfolio_images' => ! empty($this->portfolioImages),
            'social_media' => ! empty($this->socialMedia),
            default => isset($this->raw[$name]),
        };
    }

    public function toArray(): array
    {
        return array_merge($this->raw, [
            'id' => $this->id,
            'artist_name' => $this->artistName,
            'name' => $this->name,
            'bio' => $this->bio,
            'profile_image' => $this->profileImage,
            'location' => $this->location,
            'travel_distance' => $this->travelDistance,
            'website' => $this->website,
            'interests' => $this->interests,
            'portfolio_images' => $this->portfolioImages,
            'social_media' => $this->socialMedia,
            'links' => $this->links,
        ]);
    }
}
