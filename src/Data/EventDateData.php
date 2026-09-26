<?php

namespace VenueFamily\Data;

use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class EventDateData
{
    public readonly ?string $name;

    public readonly ?string $title;

    public readonly ?string $description;

    public readonly ?string $eventStart;

    public readonly ?string $eventEnd;

    /**
     * @param  PhaseData[]  $phases
     * @param  MediaData[]  $images
     */
    public function __construct(
        public readonly int $id,
        public readonly ?string $date = null,
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?string $doorTime = null,
        public readonly ?string $status = null,
        public readonly bool $isSoldOut = false,
        public readonly ?int $ticketsRemaining = null,
        public readonly array $locations = [],
        public readonly array $phases = [],
        public readonly ?TicketingData $ticketing = null,
        public readonly array $images = [],
        public readonly ?EventData $event = null,
        public readonly array $raw = [],
        ?string $title = null,
        ?string $name = null,
        ?string $description = null,
        ?string $eventStart = null,
        ?string $eventEnd = null,
    ) {
        $this->title = $title ?? $name ?? $this->raw['title'] ?? $this->raw['name'] ?? $this->event?->title;
        $this->name = $this->title;
        $this->description = $description ?? $this->raw['description'] ?? $this->event?->description;
        $this->eventStart = $eventStart ?? $this->startTime;
        $this->eventEnd = $eventEnd ?? $this->endTime;
    }

    public static function fromArray(array $data): self
    {
        $phases = [];
        if (! empty($data['phases']) && is_array($data['phases'])) {
            foreach ($data['phases'] as $phaseArr) {
                $phases[] = PhaseData::fromArray($phaseArr);
            }
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

        $event = null;
        if (! empty($data['event']) && is_array($data['event'])) {
            $event = EventData::fromArray($data['event']);
        } elseif (isset($data['event']) && $data['event'] instanceof EventData) {
            $event = $data['event'];
        }

        $isSoldOut = (bool) ($data['is_sold_out'] ?? ($ticketing?->soldOut ?? false));

        return new self(
            id: (int) ($data['id'] ?? 0),
            date: $data['date'] ?? null,
            startTime: $data['starts_at'] ?? $data['event_start'] ?? $data['start_time'] ?? null,
            endTime: $data['ends_at'] ?? $data['event_end'] ?? $data['end_time'] ?? null,
            doorTime: $data['door_time'] ?? null,
            status: $data['status'] ?? null,
            isSoldOut: $isSoldOut,
            ticketsRemaining: isset($data['tickets_remaining']) ? (int) $data['tickets_remaining'] : null,
            locations: $data['locations'] ?? [],
            phases: $phases,
            ticketing: $ticketing,
            images: $images,
            event: $event,
            raw: $data,
            title: $data['title'] ?? $data['name'] ?? null,
            description: $data['description'] ?? null,
            eventStart: $data['event_start'] ?? $data['starts_at'] ?? $data['start_time'] ?? null,
            eventEnd: $data['event_end'] ?? $data['ends_at'] ?? $data['end_time'] ?? null,
        );
    }

    public function isOnSale(): bool
    {
        return $this->ticketing?->onSale ?? (! $this->isSoldOut);
    }

    /**
     * Find the first media item assigned to a specific slot,
     * falling back to the parent event if not overridden.
     */
    public function mediaForSlot(string $slot): ?MediaData
    {
        foreach ($this->images as $image) {
            if ($image->hasSlot($slot) || $image->slot === $slot) {
                return $image;
            }
        }

        return $this->event?->mediaForSlot($slot);
    }

    public function bannerImage(): ?MediaData
    {
        return $this->mediaForSlot('banner')
            ?? ($this->images[0] ?? null)
            ?? $this->event?->bannerImage();
    }

    public function listImage(): ?MediaData
    {
        return $this->mediaForSlot('list');
    }

    public function pageImage(): ?MediaData
    {
        return $this->mediaForSlot('page');
    }

    public function effectivePageImage(): ?MediaData
    {
        return $this->pageImage();
    }

    public function effectiveSubtitle(): ?string
    {
        return $this->raw['subtitle'] ?? $this->event?->raw['subtitle'] ?? null;
    }

    public function effectiveDescription(): ?string
    {
        $clean = $this->cleanDescription();

        return $clean !== '' ? $clean : ($this->event?->cleanDescription() ?? $this->description ?? '');
    }

    public function cleanDescription(): string
    {
        $desc = $this->description ?? $this->event?->description;
        if ($desc === null || trim($desc) === '') {
            return '';
        }

        $desc = str_replace(['\<', '\>', '\/'], ['<', '>', '/'], $desc);
        $desc = preg_replace('/<\/?(font|o:p)[^>]*>/i', '', $desc);
        $desc = preg_replace('/\s*style=(?:"[^"]*"|\'[^\']*\')/i', '', $desc);
        $desc = preg_replace('/\s*class=(?:"[^"]*(?:Mso|font)[^"]*"|\'[^\']*(?:Mso|font)[^\']*\')/i', '', $desc);
        $desc = preg_replace('/<span\s*>([\s\S]*?)<\/span>/i', '$1', $desc);
        $desc = preg_replace('/<\/?span[^>]*>/i', '', $desc);

        return trim($desc);
    }

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
     * Generate Schema.org Event JSON-LD structured data array for this date.
     */
    public function toSchemaOrg(array $overrides = []): array
    {
        $startDate = null;
        $endDate = null;

        $dtStart = $this->startTime ?? ($this->date ? $this->date.' 00:00:00' : null);
        if ($dtStart) {
            try {
                $startDate = (new \DateTimeImmutable($dtStart))->format('c');
            } catch (\Throwable) {
                $startDate = $dtStart;
            }
        }

        if ($this->endTime) {
            try {
                $endDate = (new \DateTimeImmutable($this->endTime))->format('c');
            } catch (\Throwable) {
                $endDate = $this->endTime;
            }
        }

        $image = $this->bannerImage()?->url ?? $this->pageImage()?->url ?? null;
        $canonical = $this->event?->raw['canonical_url'] ?? $this->raw['canonical_url'] ?? $this->raw['url'] ?? null;
        $url = $canonical ? $canonical.'#date-'.$this->id : null;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $this->title ?? 'Venue Family Event',
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
        if (! empty($this->locations)) {
            $firstLoc = $this->locations[0];
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
        $ticketing = $this->ticketing ?? $this->event?->ticketing;
        if ($ticketing !== null && $ticketing->hasTickets) {
            $offerType = $ticketing->isSlidingScale() ? 'AggregateOffer' : 'Offer';
            $offers = [
                '@type' => $offerType,
                'priceCurrency' => $ticketing->currency ?? 'USD',
                'availability' => ($this->isSoldOut || $ticketing->soldOut) ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
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

        return array_merge($schema, $overrides);
    }

    public function toSchemaOrgJson(array $overrides = [], int $flags = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toSchemaOrg($overrides), $flags);
    }

    public function toSchemaOrgScript(array $overrides = []): string
    {
        return sprintf('<script type="application/ld+json">%s</script>', $this->toSchemaOrgJson($overrides, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Get all media items in the gallery slot, ordered by sort_order.
     * Falls back to the parent event's gallery if this date has no gallery images.
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

        if (! empty($gallery)) {
            return $gallery;
        }

        return $this->event?->galleryMedia() ?? [];
    }

    public function withEvent(EventData $event): self
    {
        return new self(
            id: $this->id,
            date: $this->date,
            startTime: $this->startTime,
            endTime: $this->endTime,
            doorTime: $this->doorTime,
            status: $this->status,
            isSoldOut: $this->isSoldOut,
            ticketsRemaining: $this->ticketsRemaining,
            locations: $this->locations,
            phases: $this->phases,
            ticketing: $this->ticketing,
            images: $this->images,
            event: $event,
            raw: $this->raw,
            title: $this->raw['title'] ?? $this->raw['name'] ?? null,
            description: $this->raw['description'] ?? null,
            eventStart: $this->eventStart,
            eventEnd: $this->eventEnd,
        );
    }

    /**
     * Generate an RFC 5545 iCalendar string for this single event date.
     */
    public function toIcs(?string $prodId = '-//Venue Family//Event Calendar//EN'): string
    {
        $summary = $this->title ?? $this->event?->title ?? 'Venue Family Event';
        $description = $this->description ?? $this->event?->description ?? '';

        $dtStart = $this->startTime ?? ($this->date ? $this->date.' 00:00:00' : 'now');
        $dtEnd = $this->endTime;

        try {
            $startDate = new \DateTimeImmutable($dtStart);
        } catch (\Throwable) {
            $startDate = new \DateTimeImmutable('now');
        }

        if ($dtEnd) {
            try {
                $endDate = new \DateTimeImmutable($dtEnd);
            } catch (\Throwable) {
                $endDate = $startDate->modify('+2 hours');
            }
        } else {
            $endDate = $startDate->modify('+2 hours');
        }

        $dtStamp = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Ymd\THis\Z');
        $dtStartFormatted = (new \DateTimeImmutable($startDate->format('c')))->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
        $dtEndFormatted = (new \DateTimeImmutable($endDate->format('c')))->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');

        $uid = 'event-date-'.$this->id.'@venuefamily.com';

        $locationStr = '';
        if (! empty($this->locations)) {
            $locNames = array_map(function ($loc) {
                return is_array($loc) ? ($loc['name'] ?? '') : (string) $loc;
            }, $this->locations);
            $locationStr = implode(', ', array_filter($locNames));
        }

        $escapeIcs = function (string $text): string {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(';', '\;', $text);
            $text = str_replace(',', '\,', $text);

            return str_replace(["\r\n", "\n", "\r"], '\n', $text);
        };

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:'.$prodId,
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$dtStamp,
            'DTSTART:'.$dtStartFormatted,
            'DTEND:'.$dtEndFormatted,
            'SUMMARY:'.$escapeIcs($summary),
        ];

        if ($description !== '') {
            $lines[] = 'DESCRIPTION:'.$escapeIcs($description);
        }

        if ($locationStr !== '') {
            $lines[] = 'LOCATION:'.$escapeIcs($locationStr);
        }

        $lines[] = 'STATUS:'.($this->status ? strtoupper($this->status) : 'CONFIRMED');
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'name', 'title' => $this->title,
            'description' => $this->description,
            'event_start', 'eventStart', 'start_time', 'startTime' => $this->startTime,
            'event_end', 'eventEnd', 'end_time', 'endTime' => $this->endTime,
            'is_sold_out', 'isSoldOut' => $this->isSoldOut,
            'door_time', 'doorTime' => $this->doorTime,
            'tickets_remaining', 'ticketsRemaining' => $this->ticketsRemaining,
            default => $this->raw[$name] ?? $this->event?->$name,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'name', 'title' => $this->title !== null,
            'description' => $this->description !== null,
            'event_start', 'eventStart', 'start_time', 'startTime' => $this->startTime !== null,
            'event_end', 'eventEnd', 'end_time', 'endTime' => $this->endTime !== null,
            'is_sold_out', 'isSoldOut' => true,
            'door_time', 'doorTime' => $this->doorTime !== null,
            'tickets_remaining', 'ticketsRemaining' => $this->ticketsRemaining !== null,
            default => isset($this->raw[$name]) || (isset($this->event) && isset($this->event->$name)),
        };
    }

    public function toArray(): array
    {
        return array_merge($this->raw, [
            'id' => $this->id,
            'date' => $this->date,
            'start_time' => $this->startTime,
            'startTime' => $this->startTime,
            'end_time' => $this->endTime,
            'endTime' => $this->endTime,
            'event_start' => $this->eventStart,
            'eventStart' => $this->eventStart,
            'event_end' => $this->eventEnd,
            'eventEnd' => $this->eventEnd,
            'door_time' => $this->doorTime,
            'doorTime' => $this->doorTime,
            'title' => $this->title,
            'name' => $this->name,
            'description' => $this->description,
            'is_sold_out' => $this->isSoldOut,
            'isSoldOut' => $this->isSoldOut,
            'tickets_remaining' => $this->ticketsRemaining,
            'ticketsRemaining' => $this->ticketsRemaining,
        ]);
    }
}
