<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\LocationData;

class LocationsResource extends BaseResource
{
    /**
     * List locations for the active organization with optional query filters.
     *
     * @return LocationData[]
     */
    public function all(array $query = []): array
    {
        $response = $this->client->get($this->orgPath('locations'), $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => LocationData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve details for a single location by ID or stub.
     */
    public function find(int|string $locationIdOrStub): LocationData
    {
        $response = $this->client->get($this->orgPath("locations/{$locationIdOrStub}"));
        $item = $response['data'] ?? $response;

        return LocationData::fromArray($item);
    }

    /**
     * Retrieve GeoJSON map data for locations in the active organization.
     */
    public function mapData(): array
    {
        return $this->client->get($this->orgPath('locations/map'));
    }
}
