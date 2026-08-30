<?php

namespace VenueFamily\Data;

class EventData
{
    /**
     * @param  EventDateData[]  $upcomingDates
     * @param  EventDateData[]  $allDates
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
        public readonly ?TicketingData $ticketing = null,
        public readonly ?EventDateData $nextDate = null,
        public readonly array $tags = [],
        public readonly array $locations = [],
        public readonly array $upcomingDates = [],
        public readonly array $allDates = [],
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

        $allDates = [];
        if (! empty($data['all_dates']) && is_array($data['all_dates'])) {
            foreach ($data['all_dates'] as $dateArr) {
                $allDates[] = EventDateData::fromArray($dateArr);
            }
        } elseif (! empty($data['event_dates']) && is_array($data['event_dates'])) {
            foreach ($data['event_dates'] as $dateArr) {
                $allDates[] = EventDateData::fromArray($dateArr);
            }
        }

        $nextDate = null;
        if (! empty($data['next_date']) && is_array($data['next_date'])) {
            $nextDate = EventDateData::fromArray($data['next_date']);
        }

        $ticketing = null;
        if (! empty($data['ticketing']) && is_array($data['ticketing'])) {
            $ticketing = TicketingData::fromArray($data['ticketing']);
        }

        $title = $data['title'] ?? $data['name'] ?? '';
        $coverImageUrl = $data['cover_image_url'] ?? $data['image_url'] ?? null;
        $ticketPrice = isset($data['ticket_price']) ? (string) $data['ticket_price'] : ($ticketing?->formattedPriceRange);

        return new self(
            id: (int) ($data['id'] ?? 0),
            title: $title,
            slug: $data['slug'] ?? null,
            subtitle: $data['subtitle'] ?? null,
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            status: $data['status'] ?? null,
            coverImageUrl: $coverImageUrl,
            ticketPrice: $ticketPrice,
            ticketing: $ticketing,
            nextDate: $nextDate,
            tags: $data['tags'] ?? [],
            locations: $data['locations'] ?? [],
            upcomingDates: $dates,
            allDates: $allDates,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
