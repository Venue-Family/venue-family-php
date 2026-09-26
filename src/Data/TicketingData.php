<?php

namespace VenueFamily\Data;

class TicketingData
{
    /**
     * @param  TicketTypeData[]  $ticketTypes
     */
    public function __construct(
        public readonly bool $hasTickets = false,
        public readonly bool $onSale = false,
        public readonly bool $soldOut = false,
        public readonly ?string $provider = null,
        public readonly string $currency = 'USD',
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly ?string $formattedPriceRange = null,
        public readonly ?string $purchaseUrl = null,
        public readonly ?string $embedPurchaseUrl = null,
        public readonly ?string $salesStartAt = null,
        public readonly ?string $salesEndAt = null,
        public readonly array $ticketTypes = [],
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        $ticketTypes = [];
        if (! empty($data['ticket_types']) && is_array($data['ticket_types'])) {
            foreach ($data['ticket_types'] as $typeArr) {
                $ticketTypes[] = TicketTypeData::fromArray($typeArr);
            }
        }

        return new self(
            hasTickets: (bool) ($data['has_tickets'] ?? false),
            onSale: (bool) ($data['on_sale'] ?? false),
            soldOut: (bool) ($data['sold_out'] ?? false),
            provider: $data['provider'] ?? null,
            currency: $data['currency'] ?? 'USD',
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            formattedPriceRange: $data['formatted_price_range'] ?? null,
            purchaseUrl: $data['purchase_url'] ?? null,
            embedPurchaseUrl: $data['embed_purchase_url'] ?? null,
            salesStartAt: $data['sales_start_at'] ?? null,
            salesEndAt: $data['sales_end_at'] ?? null,
            ticketTypes: $ticketTypes,
            raw: $data
        );
    }

    public function isBuyable(): bool
    {
        return $this->hasTickets
            && $this->onSale
            && ! $this->soldOut;
    }

    public function isFree(): bool
    {
        if (strcasecmp((string) $this->formattedPriceRange, 'Free') === 0) {
            return true;
        }

        if ($this->hasTickets && $this->minPrice !== null && $this->maxPrice !== null) {
            return (float) $this->minPrice === 0.0 && (float) $this->maxPrice === 0.0;
        }

        return false;
    }

    public function isSlidingScale(): bool
    {
        if ($this->minPrice !== null && $this->maxPrice !== null) {
            return (float) $this->minPrice < (float) $this->maxPrice;
        }

        return ! empty($this->formattedPriceRange)
            && (str_contains($this->formattedPriceRange, '–') || str_contains($this->formattedPriceRange, '-'));
    }

    public function isSlidingScaleFromZero(): bool
    {
        if (! $this->isSlidingScale()) {
            return false;
        }

        if ($this->minPrice !== null) {
            return (float) $this->minPrice === 0.0;
        }

        return str_starts_with(trim((string) $this->formattedPriceRange), '$0');
    }

    public function buttonLabel(): string
    {
        if ($this->soldOut) {
            return 'Sold Out';
        }

        if (! $this->onSale) {
            return 'Sales Closed';
        }

        if (! $this->hasTickets) {
            return 'Tickets';
        }

        if ($this->isFree()) {
            return 'RSVP Free';
        }

        if ($this->isSlidingScaleFromZero()) {
            $maxFormatted = $this->maxPrice !== null
                ? (floor($this->maxPrice) == $this->maxPrice ? '$'.number_format($this->maxPrice, 0) : '$'.number_format($this->maxPrice, 2))
                : '';
            $range = $maxFormatted ? "Free – {$maxFormatted}" : 'Free';

            return "Register ({$range})";
        }

        if ($this->isSlidingScale()) {
            if (! empty($this->formattedPriceRange)) {
                return "Tickets ({$this->formattedPriceRange})";
            }

            $minFormatted = floor($this->minPrice) == $this->minPrice ? '$'.number_format($this->minPrice, 0) : '$'.number_format($this->minPrice, 2);
            $maxFormatted = floor($this->maxPrice) == $this->maxPrice ? '$'.number_format($this->maxPrice, 0) : '$'.number_format($this->maxPrice, 2);

            return "Tickets ({$minFormatted} – {$maxFormatted})";
        }

        if (! empty($this->formattedPriceRange)) {
            return "Get Tickets ({$this->formattedPriceRange})";
        }

        if ($this->minPrice !== null) {
            $formatted = floor($this->minPrice) == $this->minPrice
                ? '$'.number_format($this->minPrice, 0)
                : '$'.number_format($this->minPrice, 2);

            return "Get Tickets ({$formatted})";
        }

        return 'Get Tickets';
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
