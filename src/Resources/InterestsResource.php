<?php

namespace VenueFamily\Resources;

class InterestsResource extends BaseResource
{
    /**
     * Get interest categories tree.
     */
    public function tree(): array
    {
        return $this->client->get('interests');
    }

    /**
     * Get flattened interest list.
     */
    public function flat(): array
    {
        return $this->client->get('interests/flat');
    }

    /**
     * Find single interest category by ID.
     */
    public function find(int $id): array
    {
        return $this->client->get("interests/{$id}");
    }
}
