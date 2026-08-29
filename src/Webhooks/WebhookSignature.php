<?php

namespace VenueFamily\Webhooks;

use VenueFamily\Exceptions\VenueFamilyException;

class WebhookSignature
{
    /**
     * Verify an incoming webhook signature using HMAC-SHA256.
     */
    public static function verify(string $payload, string $signatureHeader, string $secret): bool
    {
        if (empty($signatureHeader) || empty($secret)) {
            return false;
        }

        $provided = $signatureHeader;
        if (str_starts_with($provided, 'sha256=')) {
            $provided = substr($provided, 7);
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $provided);
    }

    /**
     * Generate an HMAC-SHA256 signature for an outgoing payload.
     */
    public static function sign(string $payload, string $secret): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify signature and return parsed JSON payload.
     *
     * @throws VenueFamilyException
     */
    public static function constructEvent(string $payload, string $signatureHeader, string $secret): array
    {
        if (! self::verify($payload, $signatureHeader, $secret)) {
            throw new VenueFamilyException('Invalid webhook signature');
        }

        try {
            return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new VenueFamilyException('Invalid webhook payload: '.$e->getMessage(), 0, null);
        }
    }
}
