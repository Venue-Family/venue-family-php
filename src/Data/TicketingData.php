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

    public function toArray(): array
    {
        return $this->raw;
    }
}
