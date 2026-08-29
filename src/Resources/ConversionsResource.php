<?php

namespace VenueFamily\Resources;

class ConversionsResource extends BaseResource
{
    /**
     * Submit an authenticated conversion tracking postback.
     */
    public function postback(string $token, string $secret, array $payload): array
    {
        $payload['token'] = $token;
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $signature = 'sha256='.hash_hmac('sha256', $json, $secret);

        return $this->client->sendRawRequest('POST', 'track/conversion', [
            'body' => $json,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-VF-Signature' => $signature,
            ],
        ]);
    }
}
