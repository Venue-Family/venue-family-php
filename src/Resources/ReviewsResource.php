<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\ReviewData;

class ReviewsResource extends BaseResource
{
    /**
     * List reviews (requires organization API key/auth).
     *
     * @return ReviewData[]
     */
    public function all(array $query = []): array
    {
        $response = $this->client->get('reviews', $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => ReviewData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Create a review.
     */
    public function create(array $payload): ReviewData
    {
        $response = $this->client->post('reviews', $payload);
        $item = $response['data'] ?? $response;

        return ReviewData::fromArray($item);
    }

    /**
     * Update status of a review (approved, rejected, pending).
     */
    public function updateStatus(int $reviewId, string $status): array
    {
        return $this->client->patch("reviews/{$reviewId}/status", [
            'status' => $status,
        ]);
    }

    /**
     * Get review aggregate statistics.
     */
    public function stats(): array
    {
        return $this->client->get('reviews/stats');
    }
}
