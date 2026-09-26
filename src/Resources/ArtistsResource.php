<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\ArtistData;

class ArtistsResource extends BaseResource
{
    /**
     * Get all published artists in the marketplace for this organization.
     *
     * @return ArtistData[]
     */
    public function all(array $query = []): array
    {
        $response = $this->client->get($this->orgPath('marketplace/artists'), $query);

        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => ArtistData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Find a single artist by ID or slug.
     */
    public function find(int|string $idOrSlug): ArtistData
    {
        $response = $this->client->get($this->orgPath("marketplace/artists/{$idOrSlug}"));

        return ArtistData::fromArray($response['data'] ?? $response);
    }

    /**
     * Get marketplace artist interests/tags.
     */
    public function interests(): array
    {
        $response = $this->client->get($this->orgPath('marketplace/interests'));

        return $response['data'] ?? $response;
    }

    /**
     * Scope the query to a specific organization.
     */
    public function forOrganization(string $organization): self
    {
        return new self($this->client->forOrganization($organization));
    }
}
