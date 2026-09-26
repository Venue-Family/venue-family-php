<?php

namespace VenueFamily\Laravel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VenueFamily\Laravel\Events\VenueFamilyEventUpdated;
use VenueFamily\Laravel\Events\VenueFamilyTicketsSoldOut;
use VenueFamily\Laravel\Events\VenueFamilyWebhookReceived;
use VenueFamily\VenueFamilyClient;
use VenueFamily\Webhooks\WebhookSignature;

class VenueFamilyWebhookController
{
    /**
     * Handle the incoming Venue Family webhook.
     */
    public function __invoke(Request $request, VenueFamilyClient $client): JsonResponse
    {
        $secret = config('venue-family.webhook_secret');

        if (! empty($secret)) {
            $header = $request->header('X-VF-Signature') ?? $request->header('X-Signature');

            if (empty($header) || ! WebhookSignature::verify($request->getContent(), $header, $secret)) {
                return response()->json(['error' => 'Invalid webhook signature.'], Response::HTTP_UNAUTHORIZED);
            }
        }

        $payload = $request->all();
        $eventType = (string) ($payload['event'] ?? $payload['type'] ?? $payload['action'] ?? 'unknown');
        $data = $payload['data'] ?? $payload;

        // Invalidate client cache upon receiving webhooks
        $client->flushCache();

        // Dispatch general webhook event
        VenueFamilyWebhookReceived::dispatch($payload, $eventType);

        // Dispatch domain-specific events
        if (str_starts_with($eventType, 'event.') || in_array($eventType, ['event.updated', 'event.created', 'event.deleted', 'event.saved'])) {
            $eventId = $data['id'] ?? $data['event_id'] ?? null;
            $slug = $data['slug'] ?? null;
            VenueFamilyEventUpdated::dispatch($payload, $eventId, $slug);
        }

        if (
            str_contains($eventType, 'sold_out')
            || in_array($eventType, ['tickets.sold_out', 'ticketing.sold_out', 'event_date.sold_out'])
            || (! empty($data['is_sold_out']))
        ) {
            $eventId = $data['event_id'] ?? $data['id'] ?? null;
            $eventDateId = $data['event_date_id'] ?? null;
            VenueFamilyTicketsSoldOut::dispatch($payload, $eventId, $eventDateId);
        }

        return response()->json(['status' => 'success']);
    }
}
