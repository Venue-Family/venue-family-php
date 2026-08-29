<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\EventData;
use VenueFamily\Data\EventDateData;

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
}
