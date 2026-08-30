<?php

namespace VenueFamily\Data;

class EventDateData
{
    /**
     * @param  PhaseData[]  $phases
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
        public readonly array $raw = []
    ) {}

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
            raw: $data
        );
    }

    public function isOnSale(): bool
    {
        return $this->ticketing?->onSale ?? (! $this->isSoldOut);
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
