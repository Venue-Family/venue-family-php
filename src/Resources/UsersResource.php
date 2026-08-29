<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\UserData;
use VenueFamily\Data\VolunteerRoleData;

class UsersResource extends BaseResource
{
    /**
     * Retrieve the currently authenticated user profile.
     */
    public function me(): UserData
    {
        $response = $this->client->get('auth/user');
        $item = $response['user'] ?? $response['data'] ?? $response;

        return UserData::fromArray($item);
    }

    /**
     * Update current user profile.
     */
    public function updateProfile(array $attributes): UserData
    {
        $response = $this->client->put('auth/profile', $attributes);
        $item = $response['user'] ?? $response['data'] ?? $response;

        return UserData::fromArray($item);
    }

    /**
     * Get the user's active volunteer roles.
     *
     * @return VolunteerRoleData[]
     */
    public function myVolunteerRoles(): array
    {
        $response = $this->client->get('volunteer-roles/my-roles');
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => VolunteerRoleData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * List available volunteer opportunities.
     *
     * @return VolunteerRoleData[]
     */
    public function volunteerOpportunities(array $query = []): array
    {
        $response = $this->client->get('volunteer-opportunities', $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => VolunteerRoleData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Sign up for a volunteer role.
     */
    public function signUpForVolunteerRole(int $roleId, array $extra = []): array
    {
        $extra['role_id'] = $roleId;

        return $this->client->post('volunteer-roles/sign-up', $extra);
    }

    /**
     * Cancel an active volunteer role assignment.
     */
    public function cancelVolunteerRole(int $roleId): array
    {
        return $this->client->post('volunteer-roles/cancel', [
            'role_id' => $roleId,
        ]);
    }
}
