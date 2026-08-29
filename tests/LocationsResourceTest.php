<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class LocationsResourceTest extends TestCase
{
    public function test_it_fetches_and_hydrates_locations(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'locations' => [
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Main Theater',
                        'stub' => 'main-theater',
                        'capacity' => 250,
                        'price_per_hour' => '125.00',
                        'city' => 'Santa Cruz',
                        'state' => 'CA',
                    ],
                ],
            ],
        ]);

        $locations = $client->locations()->all(['city' => 'Santa Cruz']);

        $this->assertCount(1, $locations);
        $this->assertSame('Main Theater', $locations[0]->name);
        $this->assertSame(250, $locations[0]->capacity);
        $this->assertSame('125.00', $locations[0]->pricePerHour);
    }
}
