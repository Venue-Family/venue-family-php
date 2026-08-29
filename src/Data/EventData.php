<?php

namespace VenueFamily\Data;

class EventData
{
    /**
     * @param  EventDateData[]  $upcomingDates
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $slug = null,
        public readonly ?string $subtitle = null,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?string $status = null,
        public readonly ?string $coverImageUrl = null,
        public readonly ?string $ticketPrice = null,
        public readonly array $tags = [],
        public readonly array $locations = [],
        public readonly array $upcomingDates = [],
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        $dates = [];
        if (! empty($data['upcoming_dates']) && is_array($data['upcoming_dates'])) {
            foreach ($data['upcoming_dates'] as $dateArr) {
                $dates[] = EventDateData::fromArray($dateArr);
            }
        }

        return new self(
            id: (int) ($data['id'] ?? 0),
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? null,
            subtitle: $data['subtitle'] ?? null,
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            status: $data['status'] ?? null,
            coverImageUrl: $data['cover_image_url'] ?? null,
            ticketPrice: isset($data['ticket_price']) ? (string) $data['ticket_price'] : null,
            tags: $data['tags'] ?? [],
            locations: $data['locations'] ?? [],
            upcomingDates: $dates,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
