<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class VolunteeringResourceTest extends TestCase
{
    private VenueFamilyClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = VenueFamilyClient::make(
            apiKey: 'token',
            organization: 'the-418-project'
        );
    }

    public function test_it_fetches_volunteer_opportunities(): void
    {
        $this->client->fake([
            'volunteer-opportunities' => [
                'data' => [
                    [
                        'id' => 101,
                        'event_id' => 42,
                        'shift_type' => 'Door Greeter',
                        'title' => 'Friday Dance Wave',
                        'description' => 'Help greet dancers at the front desk',
                        'date' => '2026-10-16',
                        'time' => '18:30:00',
                        'starts_at' => '2026-10-16 18:30:00',
                        'ends_at' => '2026-10-16 21:00:00',
                        'location' => 'Main Lobby',
                        'roles' => [
                            [
                                'id' => 101,
                                'name' => 'Door Greeter',
                                'spots_available' => 3,
                                'is_filled' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $opportunities = $this->client->volunteering()->opportunities(['event_id' => 42]);

        $this->assertCount(1, $opportunities);
        $opp = $opportunities[0];
        $this->assertSame(101, $opp->id);
        $this->assertSame(42, $opp->eventId);
        $this->assertSame('Door Greeter', $opp->shiftType);
        $this->assertSame('Friday Dance Wave', $opp->title);
        $this->assertSame(3, $opp->spotsAvailable);
        $this->assertFalse($opp->isFilled);
    }

    public function test_it_signs_up_and_cancels_shift(): void
    {
        $this->client->fake([
            'volunteer-roles/sign-up' => [
                'success' => true,
                'message' => 'Signed up successfully',
            ],
            'volunteer-roles/cancel' => [
                'success' => true,
                'message' => 'Cancelled successfully',
            ],
        ]);

        $signup = $this->client->volunteering()->signUp(101, ['notes' => 'Looking forward to it']);
        $this->assertTrue($signup['success']);

        $cancel = $this->client->volunteering()->cancel(101);
        $this->assertTrue($cancel['success']);
    }
}
