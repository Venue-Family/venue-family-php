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

    public function test_it_hydrates_event_phases_and_ticketing_dtos(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'events/42' => [
                'data' => [
                    'id' => 42,
                    'title' => 'Spring Dance Festival',
                    'ticketing' => [
                        'has_tickets' => true,
                        'on_sale' => true,
                        'sold_out' => false,
                        'provider' => 'venue',
                        'currency' => 'USD',
                        'min_price' => 20.0,
                        'max_price' => 45.0,
                        'formatted_price_range' => '$20.00 – $45.00',
                        'ticket_types' => [
                            [
                                'id' => 'general_admission',
                                'name' => 'General Admission',
                                'price' => 25.0,
                                'available' => 50,
                                'on_sale' => true,
                            ],
                        ],
                    ],
                    'all_dates' => [
                        [
                            'id' => 108,
                            'date' => '2026-09-04',
                            'phases' => [
                                [
                                    'key' => 'phase_workshop',
                                    'name' => 'Workshop',
                                    'start_time' => '18:00',
                                    'end_time' => '19:30',
                                    'capacity' => 40,
                                ],
                            ],
                            'ticketing' => [
                                'has_tickets' => true,
                                'on_sale' => true,
                                'sold_out' => false,
                                'min_price' => 25.0,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $event = $client->events()->find(42);

        $this->assertNotNull($event->ticketing);
        $this->assertTrue($event->ticketing->hasTickets);
        $this->assertSame('$20.00 – $45.00', $event->ticketing->formattedPriceRange);
        $this->assertCount(1, $event->ticketing->ticketTypes);
        $this->assertSame('General Admission', $event->ticketing->ticketTypes[0]->name);

        $this->assertCount(1, $event->allDates);
        $date = $event->allDates[0];
        $this->assertCount(1, $date->phases);
        $this->assertSame('Workshop', $date->phases[0]->name);
        $this->assertSame(40, $date->phases[0]->capacity);
        $this->assertTrue($date->isOnSale());
    }

    public function test_it_calls_phasing_and_ticketing_api_endpoints(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'events/42/dates/108/phases' => [
                'data' => [
                    [
                        'key' => 'phase_1',
                        'name' => 'Performance',
                        'start_time' => '20:00',
                        'end_time' => '22:00',
                    ],
                ],
            ],
            'events/42/ticket-types' => [
                'data' => [
                    'has_tickets' => true,
                    'ticket_types' => [
                        [
                            'id' => 'vip',
                            'name' => 'VIP Access',
                            'price' => 50.0,
                        ],
                    ],
                ],
            ],
        ]);

        $phases = $client->events()->savePhases(42, 108, [
            ['name' => 'Performance', 'start_time' => '20:00', 'end_time' => '22:00'],
        ]);
        $this->assertCount(1, $phases);
        $this->assertSame('Performance', $phases[0]->name);

        $ticketing = $client->events()->saveTicketTypes(42, [
            ['name' => 'VIP Access', 'price' => 50.0],
        ]);
        $this->assertTrue($ticketing->hasTickets);
        $this->assertSame('VIP Access', $ticketing->ticketTypes[0]->name);
    }

    public function test_it_throws_exception_if_no_organization_is_set_for_scoped_call(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Organization slug must be set');

        $client = new VenueFamilyClient('token', null);
        $client->events()->upcoming();
    }
}
