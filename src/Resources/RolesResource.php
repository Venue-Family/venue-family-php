<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\RoleData;

class RolesResource extends BaseResource
{
    /**
     * Inspect roles in the current organization context.
     *
     * @return RoleData[]
     */
    public function all(): array
    {
        $response = $this->client->get('roles');
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => RoleData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve a specific role definition by ID or slug.
     */
    public function find(int|string $roleIdOrSlug): RoleData
    {
        $response = $this->client->get("roles/{$roleIdOrSlug}");
        $item = $response['data'] ?? $response;

        return RoleData::fromArray($item);
    }
}
