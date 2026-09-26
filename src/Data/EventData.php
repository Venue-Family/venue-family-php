<?php

namespace VenueFamily\Data;

use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class EventData
{
    public readonly string $name;

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
        public readonly array $raw = [],
        ?string $name = null
    ) {
        $this->name = $name ?? $this->title;
    }

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
            raw: $data,
            name: $data['name'] ?? $title
        );
    }

    /**
     * Resolve active ticketing, falling back to nextDate or upcoming dates if top-level ticketing is empty.
     */
    public function activeTicketing(): ?TicketingData
    {
        if ($this->ticketing?->hasTickets) {
            return $this->ticketing;
        }

        if ($this->nextDate?->ticketing?->hasTickets) {
            return $this->nextDate->ticketing;
        }

        foreach ($this->upcomingDates as $date) {
            if ($date->ticketing?->hasTickets) {
                return $date->ticketing;
            }
        }

        return $this->ticketing ?? $this->nextDate?->ticketing;
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

    /**
     * Generate an RFC 5545 iCalendar string for this event or one of its dates.
     */
    public function toIcs(?int $eventDateId = null, ?string $prodId = '-//Venue Family//Event Calendar//EN'): string
    {
        if ($eventDateId !== null) {
            foreach (array_merge($this->upcomingDates, $this->allDates) as $date) {
                if ($date->id === $eventDateId) {
                    return $date->withEvent($this)->toIcs($prodId);
                }
            }
        }

        if ($this->nextDate !== null) {
            return $this->nextDate->withEvent($this)->toIcs($prodId);
        }

        if (! empty($this->upcomingDates)) {
            return $this->upcomingDates[0]->withEvent($this)->toIcs($prodId);
        }

        if (! empty($this->allDates)) {
            return $this->allDates[0]->withEvent($this)->toIcs($prodId);
        }

        $dummyDate = new EventDateData(
            id: $this->id,
            date: date('Y-m-d'),
            event: $this,
            raw: ['event' => $this->raw]
        );

        return $dummyDate->toIcs($prodId);
    }

    /**
     * Get sanitized event description free of inline styles, foreign fonts, and escaped brackets.
     */
    public function cleanDescription(): string
    {
        $desc = $this->description;
        if ($desc === null || trim($desc) === '') {
            return '';
        }

        // 1. Unescape backslash-escaped HTML brackets from markdown (e.g. \<span style="...">\</span>)
        $desc = str_replace(['\<', '\>', '\/'], ['<', '>', '/'], $desc);

        // 2. Strip foreign font and o:p tags
        $desc = preg_replace('/<\/?(font|o:p)[^>]*>/i', '', $desc);

        // 3. Strip inline style attributes: style="..." or style='...'
        $desc = preg_replace('/\s*style=(?:"[^"]*"|\'[^\']*\')/i', '', $desc);

        // 4. Strip Word/office classes
        $desc = preg_replace('/\s*class=(?:"[^"]*(?:Mso|font)[^"]*"|\'[^\']*(?:Mso|font)[^\']*\')/i', '', $desc);

        // 5. Unwrap spans
        $desc = preg_replace('/<span\s*>([\s\S]*?)<\/span>/i', '$1', $desc);
        $desc = preg_replace('/<\/?span[^>]*>/i', '', $desc);

        return trim($desc);
    }

    /**
     * Convert clean description from Markdown to safe HTML.
     */
    public function descriptionHtml(): string
    {
        $clean = $this->cleanDescription();
        if ($clean === '') {
            return '';
        }

        if (class_exists(Str::class) && method_exists(Str::class, 'markdown')) {
            return (string) Str::markdown($clean, ['allow_unsafe_links' => false]);
        }

        if (class_exists(CommonMarkConverter::class)) {
            $converter = new CommonMarkConverter(['allow_unsafe_links' => false]);

            return (string) $converter->convert($clean);
        }

        $html = preg_replace('/^###\s+(.*)$/m', '<h3>$1</h3>', $clean);
        $html = preg_replace('/^##\s+(.*)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^#\s+(.*)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $html);
        $html = preg_replace('/\[(.*?)\]\(((?:https?:\/\/|\/)[^\s\)]+)\)/', '<a href="$2" rel="noopener noreferrer">$1</a>', $html);

        $paragraphs = preg_split('/\n\s*\n/', trim($html));

        return implode('', array_map(fn ($p) => '<p>'.nl2br(trim($p)).'</p>', $paragraphs));
    }

    /**
     * Generate Schema.org Event JSON-LD structured data array.
     */
    public function toSchemaOrg(array $overrides = []): array
    {
        $activeDate = $this->nextDate ?? ($this->upcomingDates[0] ?? ($this->allDates[0] ?? null));
        $startDate = null;
        $endDate = null;

        if ($activeDate !== null) {
            $dtStart = $activeDate->startTime ?? ($activeDate->date ? $activeDate->date.' 00:00:00' : null);
            if ($dtStart) {
                try {
                    $startDate = (new \DateTimeImmutable($dtStart))->format('c');
                } catch (\Throwable) {
                    $startDate = $dtStart;
                }
            }

            if ($activeDate->endTime) {
                try {
                    $endDate = (new \DateTimeImmutable($activeDate->endTime))->format('c');
                } catch (\Throwable) {
                    $endDate = $activeDate->endTime;
                }
            }
        }

        $image = $this->bannerImage()?->url ?? $this->pageImage()?->url ?? null;
        $url = $this->raw['canonical_url'] ?? $this->raw['url'] ?? null;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $this->title,
            'description' => $this->cleanDescription(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        ];

        if ($startDate !== null) {
            $schema['startDate'] = $startDate;
        }

        if ($endDate !== null) {
            $schema['endDate'] = $endDate;
        }

        if ($url !== null) {
            $schema['url'] = $url;
        }

        if ($image !== null) {
            $schema['image'] = [$image];
        }

        // Location
        $locations = $activeDate?->locations ?? [];
        if (! empty($locations)) {
            $firstLoc = $locations[0];
            $locName = is_array($firstLoc) ? ($firstLoc['name'] ?? null) : (string) $firstLoc;
            $locAddr = is_array($firstLoc) ? ($firstLoc['address'] ?? null) : null;

            $place = [
                '@type' => 'Place',
                'name' => $locName,
            ];
            if ($locAddr) {
                $place['address'] = $locAddr;
            }
            $schema['location'] = $place;
        }

        // Ticketing / Offers
        $ticketing = $this->activeTicketing();
        if ($ticketing !== null && $ticketing->hasTickets) {
            $offerType = $ticketing->isSlidingScale() ? 'AggregateOffer' : 'Offer';
            $offers = [
                '@type' => $offerType,
                'priceCurrency' => $ticketing->currency ?? 'USD',
                'availability' => $ticketing->soldOut ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
            ];

            if ($ticketing->isSlidingScale()) {
                $offers['lowPrice'] = $ticketing->minPrice;
                if ($ticketing->maxPrice !== null) {
                    $offers['highPrice'] = $ticketing->maxPrice;
                }
            } elseif ($ticketing->minPrice !== null) {
                $offers['price'] = $ticketing->minPrice;
            }

            if ($ticketing->purchaseUrl ?? $ticketing->embedPurchaseUrl) {
                $offers['url'] = $ticketing->purchaseUrl ?? $ticketing->embedPurchaseUrl;
            }

            $schema['offers'] = $offers;
        }

        // Organization / Organizer
        if (! empty($this->raw['organization'])) {
            $orgName = is_array($this->raw['organization'])
                ? ($this->raw['organization']['name'] ?? null)
                : (string) $this->raw['organization'];

            if ($orgName) {
                $schema['organizer'] = [
                    '@type' => 'Organization',
                    'name' => $orgName,
                ];
            }
        }

        return array_merge($schema, $overrides);
    }

    /**
     * Generate Schema.org Event JSON-LD string.
     */
    public function toSchemaOrgJson(array $overrides = [], int $flags = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toSchemaOrg($overrides), $flags);
    }

    /**
     * Generate <script type="application/ld+json"> tag for HTML embedding.
     */
    public function toSchemaOrgScript(array $overrides = []): string
    {
        return sprintf('<script type="application/ld+json">%s</script>', $this->toSchemaOrgJson($overrides, JSON_UNESCAPED_SLASHES));
    }

    public function __get(string $name): mixed
    {
        if ($name === 'name') {
            return $this->name;
        }

        return $this->raw[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return $name === 'name' || isset($this->raw[$name]);
    }

    public function toArray(): array
    {
        return array_merge($this->raw, [
            'id' => $this->id,
            'title' => $this->title,
            'name' => $this->name,
            'slug' => $this->slug,
        ]);
    }
}
