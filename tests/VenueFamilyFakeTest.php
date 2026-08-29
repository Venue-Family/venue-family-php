<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class VenueFamilyFakeTest extends TestCase
{
    public function test_fake_records_requests_and_passes_assertions(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $fake = $client->fake([
            'events/upcoming' => [
                'data' => [
                    ['id' => 1, 'title' => 'Test Event'],
                ],
            ],
        ]);

        $client->events()->upcoming();

        $fake->assertSent(function ($method, $url, $options) {
            return $method === 'GET' && str_contains($url, 'events/upcoming');
        });

        $fake->assertSentCount(1);
        $fake->assertNotSent(function ($method, $url) {
            return $method === 'POST';
        });
    }

    public function test_assert_nothing_sent_when_no_requests_made(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $fake = $client->fake();

        $fake->assertNothingSent();
    }
}
