<?php

namespace VenueFamily\Data;

class VolunteerOpportunityData
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $eventId = null,
        public readonly ?string $shiftType = null,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $date = null,
        public readonly ?string $time = null,
        public readonly ?string $startsAt = null,
        public readonly ?string $endsAt = null,
        public readonly ?string $location = null,
        public readonly array $roles = [],
        public readonly ?int $spotsAvailable = null,
        public readonly bool $isFilled = false,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        $roles = $data['roles'] ?? [];
        $firstRole = ! empty($roles) && is_array($roles[0]) ? $roles[0] : [];

        $spots = isset($data['spots_available'])
            ? (int) $data['spots_available']
            : (isset($firstRole['spots_available']) ? (int) $firstRole['spots_available'] : null);

        $isFilled = (bool) ($data['is_filled'] ?? ($firstRole['is_filled'] ?? false));

        return new self(
            id: (int) ($data['id'] ?? 0),
            eventId: isset($data['event_id']) ? (int) $data['event_id'] : null,
            shiftType: $data['shift_type'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            date: $data['date'] ?? null,
            time: $data['time'] ?? null,
            startsAt: $data['starts_at'] ?? null,
            endsAt: $data['ends_at'] ?? null,
            location: $data['location'] ?? null,
            roles: $roles,
            spotsAvailable: $spots,
            isFilled: $isFilled,
            raw: $data
        );
    }

    public function toArray(): array
    {
        return array_merge($this->raw, [
            'id' => $this->id,
            'event_id' => $this->eventId,
            'shift_type' => $this->shiftType,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'time' => $this->time,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'location' => $this->location,
            'roles' => $this->roles,
            'spots_available' => $this->spotsAvailable,
            'is_filled' => $this->isFilled,
        ]);
    }
}
