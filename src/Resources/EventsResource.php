<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\EventData;
use VenueFamily\Data\EventDateData;
use VenueFamily\Data\PhaseData;
use VenueFamily\Data\TicketingData;

class EventsResource extends BaseResource
{
    /**
     * List events for the active organization with optional query filters.
     *
     * @return EventData[]
     */
    public function all(array $query = []): array
    {
        $response = $this->client->get($this->orgPath('events'), $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve upcoming events for the active organization.
     *
     * @param  array<string>|string  $tags
     * @return EventData[]
     */
    public function upcoming(array|string $tags = [], ?string $location = null, array $query = []): array
    {
        if (! empty($tags)) {
            $query['tags'] = is_array($tags) ? implode(',', $tags) : $tags;
        }

        if ($location !== null) {
            $query['location'] = $location;
        }

        $response = $this->client->get($this->orgPath('events/upcoming'), $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve past events for the active organization.
     *
     * @return EventData[]
     */
    public function past(array $query = []): array
    {
        $response = $this->client->get($this->orgPath('events/past'), $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve events occurring within an arbitrary date range.
     *
     * @return EventData[]
     */
    public function between(string|\DateTimeInterface $start, string|\DateTimeInterface $end, array $query = []): array
    {
        $startDate = $start instanceof \DateTimeInterface ? $start->format('Y-m-d') : $start;
        $endDate = $end instanceof \DateTimeInterface ? $end->format('Y-m-d') : $end;

        $query['start_date'] = $startDate;
        $query['end_date'] = $endDate;

        return $this->all($query);
    }

    /**
     * Retrieve recurring events for the active organization.
     *
     * @return EventData[]
     */
    public function recurring(array $query = []): array
    {
        $response = $this->client->get($this->orgPath('events/recurring'), $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Search events within the active organization.
     *
     * @return EventData[]
     */
    public function search(string $queryTerm, array $extraQuery = []): array
    {
        $extraQuery['q'] = $queryTerm;
        $response = $this->client->get($this->orgPath('events/search'), $extraQuery);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve details for a single event by ID or slug.
     */
    public function find(int|string $eventIdOrSlug): EventData
    {
        $response = $this->client->get($this->orgPath("events/{$eventIdOrSlug}"));
        $item = $response['data'] ?? $response;

        return EventData::fromArray($item);
    }

    /**
     * Retrieve dates for a specific event.
     *
     * @return EventDateData[]
     */
    public function dates(int|string $eventIdOrSlug): array
    {
        $response = $this->client->get($this->orgPath("events/{$eventIdOrSlug}/dates"));
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventDateData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve a specific event date by ID.
     */
    public function date(int $eventDateId): EventDateData
    {
        $response = $this->client->get($this->orgPath("eventDates/{$eventDateId}"));
        $item = $response['data'] ?? $response;

        return EventDateData::fromArray($item);
    }

    /**
     * Global upcoming events across all public organizations (/api/events/upcoming).
     *
     * @return EventData[]
     */
    public function globalUpcoming(array $query = []): array
    {
        $response = $this->client->get('events/upcoming', $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Global event search across all public organizations (/api/events/search).
     *
     * @return EventData[]
     */
    public function globalSearch(string $queryTerm, array $extraQuery = []): array
    {
        $extraQuery['q'] = $queryTerm;
        $response = $this->client->get('events/search', $extraQuery);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => EventData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Get day-structure phases for an event date.
     *
     * @return PhaseData[]
     */
    public function phases(int $eventId, int $eventDateId): array
    {
        $response = $this->client->get("events/{$eventId}/dates/{$eventDateId}/phases");
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => PhaseData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Save/reconcile day-structure phases for an event date.
     *
     * @param  array<int, array<string, mixed>>  $phases
     * @return PhaseData[]
     */
    public function savePhases(int $eventId, int $eventDateId, array $phases): array
    {
        $response = $this->client->put("events/{$eventId}/dates/{$eventDateId}/phases", [
            'phases' => $phases,
        ]);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => PhaseData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Get ticketing setup and ticket types for an event.
     */
    public function ticketTypes(int $eventId): TicketingData
    {
        $response = $this->client->get("events/{$eventId}/ticket-types");
        $item = $response['data'] ?? $response;

        return TicketingData::fromArray($item);
    }

    /**
     * Save/reconcile ticket types for an event.
     *
     * @param  array<int, array<string, mixed>>  $ticketTypes
     * @param  int[]  $dateIds
     */
    public function saveTicketTypes(int $eventId, array $ticketTypes, array $dateIds = []): TicketingData
    {
        $payload = ['ticket_types' => $ticketTypes];
        if (! empty($dateIds)) {
            $payload['date_ids'] = $dateIds;
        }

        $response = $this->client->put("events/{$eventId}/ticket-types", $payload);
        $item = $response['data'] ?? $response;

        return TicketingData::fromArray($item);
    }

    /**
     * Set sales window start and end across event dates.
     *
     * @param  int[]  $dateIds
     */
    public function saveSalesWindow(int $eventId, ?string $salesStartAt, ?string $salesEndAt, array $dateIds = []): array
    {
        $payload = [
            'sales_start_at' => $salesStartAt,
            'sales_end_at' => $salesEndAt,
        ];
        if (! empty($dateIds)) {
            $payload['date_ids'] = $dateIds;
        }

        return $this->client->put("events/{$eventId}/sales-window", $payload);
    }

    /**
     * Set session and phase capacities for an event date.
     *
     * @param  array<string, int|null>  $phaseCapacities
     */
    public function saveCapacities(int $eventId, int $eventDateId, ?int $dateCapacity, array $phaseCapacities = []): array
    {
        return $this->client->put("events/{$eventId}/dates/{$eventDateId}/capacities", [
            'date_capacity' => $dateCapacity,
            'phase_capacities' => $phaseCapacities,
        ]);
    }
}
