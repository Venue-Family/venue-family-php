<?php

namespace VenueFamily\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VenueFamily\Webhooks\WebhookSignature;

class VerifyVenueFamilyWebhookSignature
{
    /**
     * Handle an incoming webhook request from Venue Family.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $secret = null): Response
    {
        $signingSecret = $secret ?? config('venue-family.webhook_secret');

        if (empty($signingSecret)) {
            abort(500, 'Venue Family webhook secret is not configured.');
        }

        $header = $request->header('X-VF-Signature') ?? $request->header('X-Signature');

        if (empty($header)) {
            abort(401, 'Missing webhook signature header.');
        }

        $payload = $request->getContent();

        if (! WebhookSignature::verify($payload, $header, $signingSecret)) {
            abort(401, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
