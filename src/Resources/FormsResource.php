<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\FormData;
use VenueFamily\Data\FormSignatureData;

class FormsResource extends BaseResource
{
    /**
     * List published dynamic forms and waivers for the active organization.
     *
     * @return FormData[]
     */
    public function all(array $query = []): array
    {
        $response = $this->client->get($this->orgPath('forms'), $query);
        $items = $response['data'] ?? $response;

        return array_map(fn (array $item) => FormData::fromArray($item), is_array($items) ? $items : []);
    }

    /**
     * Retrieve a dynamic form definition by ID or slug.
     */
    public function find(int|string $formIdOrSlug): FormData
    {
        $response = $this->client->get($this->orgPath("forms/{$formIdOrSlug}"));
        $item = $response['form'] ?? $response['data'] ?? $response;

        return FormData::fromArray($item);
    }

    /**
     * Submit response data to a dynamic form.
     */
    public function submit(int|string $formIdOrSlug, array $formData): array
    {
        return $this->client->post($this->orgPath("forms/{$formIdOrSlug}/submit"), [
            'fields' => $formData,
            'data' => $formData,
        ]);
    }

    /**
     * Retrieve a signature record by signing token.
     */
    public function signature(string $signingToken): FormSignatureData
    {
        return $this->client->signatures()->find($signingToken);
    }

    /**
     * Sign a form or waiver using native electronic signature.
     */
    public function sign(
        string $signingToken,
        string $signatureData,
        string $signerName,
        ?string $signerEmail = null
    ): array {
        return $this->client->signatures()->signNative($signingToken, $signatureData, $signerName, $signerEmail);
    }
}
