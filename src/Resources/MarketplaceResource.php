<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\ArtistData;

class MarketplaceResource extends BaseResource
{
    public function artists(): ArtistsResource
    {
        return new ArtistsResource($this->client);
    }

    /**
     * @return ArtistData[]
     */
    public function all(array $query = []): array
    {
        return $this->artists()->all($query);
    }

    public function find(int|string $idOrSlug): ArtistData
    {
        return $this->artists()->find($idOrSlug);
    }

    public function interests(): array
    {
        return $this->artists()->interests();
    }
}
