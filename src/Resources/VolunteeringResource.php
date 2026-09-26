<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\VolunteerOpportunityData;

class VolunteeringResource extends BaseResource
{
    /**
     * Get volunteer opportunities for an event or organization.
     *
     * @return VolunteerOpportunityData[]
     */
    public function opportunities(array $query = []): array
    {
        $org = $this->client->getOrganization();
        if ($org && ! isset($query['organization']) && ! isset($query['organization_id'])) {
            $query['organization'] = $org;
        }

        $response = $this->client->get('volunteer-opportunities', $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => VolunteerOpportunityData::fromArray($item), $items);
    }

    /**
     * Get standalone (organization-level) volunteer opportunities.
     *
     * @return VolunteerOpportunityData[]
     */
    public function standalone(array $query = []): array
    {
        $org = $this->client->getOrganization();
        if ($org && ! isset($query['organization']) && ! isset($query['organization_id'])) {
            $query['organization'] = $org;
        }

        $response = $this->client->get('standalone-volunteer-opportunities', $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => VolunteerOpportunityData::fromArray($item), $items);
    }

    /**
     * Get volunteer opportunities for a specific event.
     *
     * @return VolunteerOpportunityData[]
     */
    public function forEvent(int $eventId, array $query = []): array
    {
        return $this->opportunities(array_merge($query, ['event_id' => $eventId]));
    }

    /**
     * Sign up for a volunteer shift.
     */
    public function signUp(int|string $shiftId, array $data = []): array
    {
        return $this->client->post('volunteer-roles/sign-up', array_merge(['shift_id' => $shiftId], $data));
    }

    /**
     * Cancel a volunteer shift signup.
     */
    public function cancel(int|string $shiftId, ?int $userId = null): array
    {
        $payload = ['shift_id' => $shiftId];
        if ($userId !== null) {
            $payload['user_id'] = $userId;
        }

        return $this->client->post('volunteer-roles/cancel', $payload);
    }
}
