<?php

namespace VenueFamily\Resources;

use VenueFamily\Data\FormSignatureData;

class SignaturesResource extends BaseResource
{
    /**
     * Retrieve status and signer details for a signature token.
     */
    public function find(string $signingToken): FormSignatureData
    {
        $response = $this->client->get("signatures/{$signingToken}");
        $item = $response['data'] ?? $response;

        return FormSignatureData::fromArray($item);
    }

    /**
     * Submit a native electronic signature (base64 image or typed).
     */
    public function signNative(
        string $signingToken,
        string $signatureData,
        string $signerName,
        ?string $signerEmail = null
    ): array {
        return $this->client->post("signatures/{$signingToken}/sign", [
            'signature_data' => $signatureData,
            'name' => $signerName,
            'email' => $signerEmail,
            'mechanism' => 'native',
        ]);
    }

    /**
     * Get the hosted countersignature / signing URL for a token.
     */
    public function getSigningUrl(string $signingToken): string
    {
        $baseUrl = $this->client->getBaseUrl();
        $rootUrl = preg_replace('/\/api\/?$/', '', $baseUrl);

        return "{$rootUrl}/forms/countersign/{$signingToken}";
    }
}
