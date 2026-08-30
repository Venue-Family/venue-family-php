<?php

namespace VenueFamily\Data;

class TicketTypeData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly string $pricingMode = 'fixed',
        public readonly ?float $price = null,
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly array $presetAmounts = [],
        public readonly string $seatingMode = 'general',
        public readonly ?int $available = null,
        public readonly bool $soldOut = false,
        public readonly bool $onSale = true,
        public readonly ?string $salesStartAt = null,
        public readonly ?string $salesEndAt = null,
        public readonly ?string $salesRefusal = null,
        public readonly bool $isPass = false,
        public readonly bool $allowsLateJoin = false,
        public readonly array $sessions = [],
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            description: $data['description'] ?? null,
            pricingMode: $data['pricing_mode'] ?? 'fixed',
            price: isset($data['price']) ? (float) $data['price'] : null,
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            presetAmounts: array_map('floatval', $data['preset_amounts'] ?? []),
            seatingMode: $data['seating_mode'] ?? 'general',
            available: isset($data['available']) ? (int) $data['available'] : null,
            soldOut: (bool) ($data['sold_out'] ?? false),
            onSale: (bool) ($data['on_sale'] ?? true),
            salesStartAt: $data['sales_start_at'] ?? null,
            salesEndAt: $data['sales_end_at'] ?? null,
            salesRefusal: $data['sales_refusal'] ?? null,
            isPass: (bool) ($data['is_pass'] ?? false),
            allowsLateJoin: (bool) ($data['allows_late_join'] ?? false),
            sessions: $data['sessions'] ?? [],
            raw: $data
        );
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
