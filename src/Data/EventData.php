<?php

namespace VenueFamily\Data;

class EventData
{
    /**
     * @param  EventDateData[]  $upcomingDates
     * @param  EventDateData[]  $allDates
     * @param  MediaData[]  $images
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
        public readonly array $images = [],
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

        $images = [];
        $rawImages = $data['images'] ?? $data['media'] ?? [];
        if (! empty($rawImages) && is_array($rawImages)) {
            foreach ($rawImages as $imageArr) {
                if (is_array($imageArr)) {
                    $images[] = MediaData::fromArray($imageArr);
                }
            }
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
            images: $images,
            raw: $data
        );
    }

    /**
     * Find the first media item assigned to a specific slot.
     */
    public function mediaForSlot(string $slot): ?MediaData
    {
        foreach ($this->images as $image) {
            if ($image->hasSlot($slot) || $image->slot === $slot) {
                return $image;
            }
        }

        return null;
    }

    public function bannerImage(): ?MediaData
    {
        return $this->mediaForSlot('banner') ?? ($this->images[0] ?? null);
    }

    public function listImage(): ?MediaData
    {
        return $this->mediaForSlot('list');
    }

    public function pageImage(): ?MediaData
    {
        return $this->mediaForSlot('page');
    }

    /**
     * Get all media items in the gallery slot, ordered by sort_order.
     *
     * @return MediaData[]
     */
    public function galleryMedia(): array
    {
        $gallery = [];
        foreach ($this->images as $image) {
            if ($image->hasSlot('gallery') || $image->slot === 'gallery') {
                $gallery[] = $image;
            }
        }

        return $gallery;
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
