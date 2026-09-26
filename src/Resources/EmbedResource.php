<?php

namespace VenueFamily\Resources;

class EmbedResource extends BaseResource
{
    /**
     * Get the base web domain (derived from API base URL by default, or overridden).
     */
    public function webBaseUrl(): string
    {
        $apiBase = $this->client->getBaseUrl();

        if (str_ends_with($apiBase, '/api')) {
            return substr($apiBase, 0, -4);
        }

        return $apiBase;
    }

    /**
     * Build an embed URL for a given path and query parameters.
     */
    public function url(string $path, array $query = []): string
    {
        $org = $this->client->getOrganization();
        if (empty($org)) {
            throw new \InvalidArgumentException('Organization slug must be set on the VenueFamilyClient to build embed URLs.');
        }

        $cleanPath = ltrim($path, '/');
        $url = "{$this->webBaseUrl()}/embed/{$org}/{$cleanPath}";

        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }

        return $url;
    }

    /**
     * Tickets embed URL for an event.
     */
    public function tickets(string $eventSlug, array $query = []): string
    {
        return $this->url("events/{$eventSlug}/tickets", $query);
    }

    /**
     * Volunteering opportunities embed URL.
     */
    public function volunteering(array $query = []): string
    {
        return $this->url('volunteering', $query);
    }

    /**
     * Form embed URL by form slug.
     */
    public function form(string $formSlug, array $query = []): string
    {
        return $this->url("forms/{$formSlug}", $query);
    }

    /**
     * Organization or artist profile embed URL.
     */
    public function profile(array $query = []): string
    {
        return $this->url('profile', $query);
    }

    /**
     * Ticket scanner / check-in embed URL.
     */
    public function checkIn(array $query = []): string
    {
        return $this->url('ticket-scanner', $query);
    }

    /**
     * Events calendar / list embed URL.
     */
    public function events(array $query = []): string
    {
        return $this->url('events', $query);
    }

    /**
     * Past events embed URL.
     */
    public function eventsPast(array $query = []): string
    {
        return $this->url('events-past', $query);
    }

    /**
     * Calendar widget embed URL.
     */
    public function widget(array $query = []): string
    {
        return $this->url('widget', $query);
    }

    /**
     * Customer orders embed URL.
     */
    public function orders(array $query = []): string
    {
        return $this->url('orders', $query);
    }

    /**
     * Dashboard embed URL.
     */
    public function dashboard(array $query = []): string
    {
        return $this->url('dashboard', $query);
    }

    /**
     * Generate an iframe HTML snippet for embedding.
     */
    public function iframe(string $url, array $attributes = []): string
    {
        $defaults = [
            'src' => $url,
            'width' => '100%',
            'height' => '600',
            'frameborder' => '0',
            'allow' => 'camera; microphone; payment',
        ];

        $attrs = array_merge($defaults, $attributes);
        $attrStrings = [];

        foreach ($attrs as $key => $value) {
            $attrStrings[] = sprintf('%s="%s"', htmlspecialchars($key, ENT_QUOTES), htmlspecialchars((string) $value, ENT_QUOTES));
        }

        return '<iframe '.implode(' ', $attrStrings).'></iframe>';
    }
}
