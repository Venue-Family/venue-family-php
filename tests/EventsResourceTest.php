<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class EventsResourceTest extends TestCase
{
    public function test_it_fetches_and_hydrates_upcoming_events(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'events/upcoming' => [
                'data' => [
                    [
                        'id' => 42,
                        'title' => 'Ecstatic Dance',
                        'slug' => 'ecstatic-dance',
                        'ticket_price' => '15.00',
                        'tags' => [['name' => 'Dance', 'slug' => 'dance']],
                        'upcoming_dates' => [
                            [
                                'id' => 108,
                                'date' => '2026-09-04',
                                'start_time' => '19:00',
                                'is_sold_out' => false,
                                'tickets_remaining' => 25,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $events = $client->events()->upcoming(['dance'], 'main-theater');

        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertSame(42, $event->id);
        $this->assertSame('Ecstatic Dance', $event->title);
        $this->assertSame('15.00', $event->ticketPrice);
        $this->assertCount(1, $event->upcomingDates);

        $date = $event->upcomingDates[0];
        $this->assertSame(108, $date->id);
        $this->assertSame('2026-09-04', $date->date);
        $this->assertSame(25, $date->ticketsRemaining);
        $this->assertFalse($date->isSoldOut);
    }

    public function test_it_throws_exception_if_no_organization_is_set_for_scoped_call(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Organization slug must be set');

        $client = new VenueFamilyClient('token', null);
        $client->events()->upcoming();
    }
}
