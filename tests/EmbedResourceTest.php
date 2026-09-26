<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class EmbedResourceTest extends TestCase
{
    private VenueFamilyClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = VenueFamilyClient::make(
            apiKey: 'token',
            organization: 'the-418-project',
            baseUrl: 'https://venuefamily.com/api'
        );
    }

    public function test_it_generates_tickets_embed_url(): void
    {
        $url = $this->client->embed()->tickets('dance-night');
        $this->assertSame('https://venuefamily.com/embed/the-418-project/events/dance-night/tickets', $url);

        $urlWithParams = $this->client->embed()->tickets('dance-night', ['theme' => 'dark']);
        $this->assertSame('https://venuefamily.com/embed/the-418-project/events/dance-night/tickets?theme=dark', $urlWithParams);
    }

    public function test_it_generates_volunteering_embed_url(): void
    {
        $url = $this->client->embed()->volunteering();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/volunteering', $url);

        $urlWithQuery = $this->client->embed()->volunteering(['role' => 'greeter']);
        $this->assertSame('https://venuefamily.com/embed/the-418-project/volunteering?role=greeter', $urlWithQuery);
    }

    public function test_it_generates_form_and_profile_embed_urls(): void
    {
        $formUrl = $this->client->embed()->form('volunteer-waiver');
        $this->assertSame('https://venuefamily.com/embed/the-418-project/forms/volunteer-waiver', $formUrl);

        $profileUrl = $this->client->embed()->profile();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/profile', $profileUrl);
    }

    public function test_it_generates_check_in_embed_url(): void
    {
        $checkInUrl = $this->client->embed()->checkIn();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/ticket-scanner', $checkInUrl);

        $checkInEventUrl = $this->client->embed()->checkIn(['event_id' => 42]);
        $this->assertSame('https://venuefamily.com/embed/the-418-project/ticket-scanner?event_id=42', $checkInEventUrl);
    }

    public function test_it_generates_events_and_events_past_embed_urls(): void
    {
        $eventsUrl = $this->client->embed()->events();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/events', $eventsUrl);

        $pastUrl = $this->client->embed()->eventsPast();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/events-past', $pastUrl);
    }

    public function test_it_generates_widget_orders_and_dashboard_embed_urls(): void
    {
        $widgetUrl = $this->client->embed()->widget();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/widget', $widgetUrl);

        $ordersUrl = $this->client->embed()->orders();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/orders', $ordersUrl);

        $dashUrl = $this->client->embed()->dashboard();
        $this->assertSame('https://venuefamily.com/embed/the-418-project/dashboard', $dashUrl);
    }

    public function test_it_generates_custom_url_and_iframe_html(): void
    {
        $customUrl = $this->client->embed()->url('custom/path', ['ref' => 'newsletter']);
        $this->assertSame('https://venuefamily.com/embed/the-418-project/custom/path?ref=newsletter', $customUrl);

        $iframe = $this->client->embed()->iframe($customUrl, [
            'width' => '100%',
            'height' => '600px',
            'id' => 'venue-frame',
        ]);

        $this->assertStringContainsString('<iframe', $iframe);
        $this->assertStringContainsString('src="https://venuefamily.com/embed/the-418-project/custom/path?ref=newsletter"', $iframe);
        $this->assertStringContainsString('width="100%"', $iframe);
        $this->assertStringContainsString('height="600px"', $iframe);
        $this->assertStringContainsString('id="venue-frame"', $iframe);

        // Test without organization throws InvalidArgumentException
        $globalClient = VenueFamilyClient::make(baseUrl: 'https://venuefamily.com/api');
        $this->expectException(\InvalidArgumentException::class);
        $globalClient->embed()->url('global/path');
    }
}
