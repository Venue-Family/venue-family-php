<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\Data\EventData;
use VenueFamily\Data\EventDateData;
use VenueFamily\Data\TicketingData;
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

    public function test_it_hydrates_event_images_and_slot_metadata(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'events/42' => [
                'data' => [
                    'id' => 42,
                    'title' => 'Spring Dance Festival',
                    'images' => [
                        [
                            'id' => 101,
                            'name' => 'banner.jpg',
                            'url' => 'https://example.test/storage/images/banner.jpg',
                            'type' => 'image',
                            'primary' => true,
                            'slot' => 'banner',
                            'slots' => [
                                ['name' => 'banner', 'slot' => 'banner', 'sort_order' => 1],
                            ],
                            'width' => 1920,
                            'height' => 960,
                            'aspect_ratio' => 2.0,
                            'aspect_ratio_label' => '2:1',
                        ],
                        [
                            'id' => 102,
                            'name' => 'flyer.jpg',
                            'url' => 'https://example.test/storage/images/flyer.jpg',
                            'type' => 'image',
                            'primary' => false,
                            'slot' => 'page',
                            'slots' => [
                                ['name' => 'page', 'slot' => 'page', 'sort_order' => 1],
                                ['name' => 'gallery', 'slot' => 'gallery', 'sort_order' => 2],
                            ],
                            'width' => 1080,
                            'height' => 1350,
                            'aspect_ratio' => 0.8,
                            'aspect_ratio_label' => '4:5',
                        ],
                        [
                            'id' => 103,
                            'name' => 'gallery-1.jpg',
                            'url' => 'https://example.test/storage/images/gallery-1.jpg',
                            'type' => 'image',
                            'primary' => false,
                            'slot' => 'gallery',
                            'slots' => [
                                ['name' => 'gallery', 'slot' => 'gallery', 'sort_order' => 1],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $event = $client->events()->find(42);

        $this->assertCount(3, $event->images);

        $banner = $event->bannerImage();
        $this->assertNotNull($banner);
        $this->assertSame(101, $banner->id);
        $this->assertTrue($banner->isBanner());
        $this->assertTrue($banner->isPrimary);
        $this->assertSame(2.0, $banner->aspectRatio);
        $this->assertSame('2:1', $banner->aspectRatioLabel);
        $this->assertCount(1, $banner->slots);
        $this->assertSame('banner', $banner->slots[0]->slot);

        $flyer = $event->pageImage();
        $this->assertNotNull($flyer);
        $this->assertSame(102, $flyer->id);
        $this->assertTrue($flyer->isPage());
        $this->assertTrue($flyer->hasSlot('gallery'));
        $this->assertFalse($flyer->isPrimary);

        $gallery = $event->galleryMedia();
        $this->assertCount(2, $gallery);
        $this->assertSame(102, $gallery[0]->id);
        $this->assertSame(103, $gallery[1]->id);

        $this->assertNull($event->listImage());
    }

    public function test_it_throws_exception_if_no_organization_is_set_for_scoped_call(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Organization slug must be set');

        $client = new VenueFamilyClient('token', null);
        $client->events()->upcoming();
    }

    public function test_event_date_media_helper_methods_fallback_to_parent_event(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'eventDates/201' => [
                'data' => [
                    'id' => 201,
                    'date' => '2026-10-15',
                    'images' => [],
                    'event' => [
                        'id' => 42,
                        'name' => 'Spring Dance Festival',
                        'images' => [
                            [
                                'id' => 101,
                                'name' => 'parent-banner.jpg',
                                'url' => 'https://example.test/storage/images/parent-banner.jpg',
                                'type' => 'image',
                                'primary' => true,
                                'slot' => 'banner',
                                'slots' => [
                                    ['name' => 'banner', 'slot' => 'banner', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'id' => 102,
                                'name' => 'parent-page.jpg',
                                'url' => 'https://example.test/storage/images/parent-page.jpg',
                                'type' => 'image',
                                'slot' => 'page',
                                'slots' => [
                                    ['name' => 'page', 'slot' => 'page', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'id' => 103,
                                'name' => 'parent-list.jpg',
                                'url' => 'https://example.test/storage/images/parent-list.jpg',
                                'type' => 'image',
                                'slot' => 'list',
                                'slots' => [
                                    ['name' => 'list', 'slot' => 'list', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'id' => 104,
                                'name' => 'parent-gallery.jpg',
                                'url' => 'https://example.test/storage/images/parent-gallery.jpg',
                                'type' => 'image',
                                'slot' => 'gallery',
                                'slots' => [
                                    ['name' => 'gallery', 'slot' => 'gallery', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $date = $client->events()->date(201);

        $this->assertNotNull($date->event);
        $this->assertSame(42, $date->event->id);
        $this->assertEmpty($date->images);

        // Fallbacks to parent event
        $banner = $date->bannerImage();
        $this->assertNotNull($banner);
        $this->assertSame(101, $banner->id);
        $this->assertTrue($banner->isBanner());

        $page = $date->pageImage();
        $this->assertNotNull($page);
        $this->assertSame(102, $page->id);
        $this->assertTrue($page->isPage());

        $list = $date->listImage();
        $this->assertNotNull($list);
        $this->assertSame(103, $list->id);
        $this->assertTrue($list->isList());

        $gallery = $date->galleryMedia();
        $this->assertCount(1, $gallery);
        $this->assertSame(104, $gallery[0]->id);
    }

    public function test_event_date_media_helper_methods_use_date_specific_overrides(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'eventDates/202' => [
                'data' => [
                    'id' => 202,
                    'date' => '2026-10-16',
                    'images' => [
                        [
                            'id' => 201,
                            'name' => 'date-page.jpg',
                            'url' => 'https://example.test/storage/images/date-page.jpg',
                            'type' => 'image',
                            'slot' => 'page',
                            'slots' => [
                                ['name' => 'page', 'slot' => 'page', 'sort_order' => 1],
                            ],
                        ],
                        [
                            'id' => 202,
                            'name' => 'date-gallery.jpg',
                            'url' => 'https://example.test/storage/images/date-gallery.jpg',
                            'type' => 'image',
                            'slot' => 'gallery',
                            'slots' => [
                                ['name' => 'gallery', 'slot' => 'gallery', 'sort_order' => 1],
                            ],
                        ],
                    ],
                    'event' => [
                        'id' => 42,
                        'name' => 'Spring Dance Festival',
                        'images' => [
                            [
                                'id' => 101,
                                'name' => 'parent-banner.jpg',
                                'url' => 'https://example.test/storage/images/parent-banner.jpg',
                                'type' => 'image',
                                'slot' => 'banner',
                                'slots' => [
                                    ['name' => 'banner', 'slot' => 'banner', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'id' => 102,
                                'name' => 'parent-page.jpg',
                                'url' => 'https://example.test/storage/images/parent-page.jpg',
                                'type' => 'image',
                                'slot' => 'page',
                                'slots' => [
                                    ['name' => 'page', 'slot' => 'page', 'sort_order' => 1],
                                ],
                            ],
                            [
                                'id' => 104,
                                'name' => 'parent-gallery.jpg',
                                'url' => 'https://example.test/storage/images/parent-gallery.jpg',
                                'type' => 'image',
                                'slot' => 'gallery',
                                'slots' => [
                                    ['name' => 'gallery', 'slot' => 'gallery', 'sort_order' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $date = $client->events()->date(202);

        // Date-specific override takes precedence
        $page = $date->pageImage();
        $this->assertNotNull($page);
        $this->assertSame(201, $page->id);

        $gallery = $date->galleryMedia();
        $this->assertCount(1, $gallery);
        $this->assertSame(202, $gallery[0]->id);

        // Banner was not overridden on the date, so it falls back to parent event
        $banner = $date->bannerImage();
        $this->assertNotNull($banner);
        $this->assertSame(101, $banner->id);
    }

    public function test_it_queries_events_between_dates(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'events' => [
                'data' => [
                    [
                        'id' => 10,
                        'title' => 'Concert Between Dates',
                        'slug' => 'concert-between-dates',
                    ],
                ],
            ],
        ]);

        $events = $client->events()->between('2026-10-01', '2026-10-31', ['tag' => 'music']);

        $this->assertCount(1, $events);
        $this->assertSame(10, $events[0]->id);
        $this->assertSame('Concert Between Dates', $events[0]->title);
    }

    public function test_ticketing_data_rich_helpers(): void
    {
        $paid = TicketingData::fromArray([
            'has_tickets' => true,
            'on_sale' => true,
            'sold_out' => false,
            'min_price' => 15.0,
            'max_price' => 30.0,
        ]);
        $this->assertTrue($paid->isBuyable());
        $this->assertFalse($paid->isFree());
        $this->assertTrue($paid->isSlidingScale());
        $this->assertFalse($paid->isSlidingScaleFromZero());
        $this->assertSame('Tickets ($15 – $30)', $paid->buttonLabel());

        $fixedPaid = TicketingData::fromArray([
            'has_tickets' => true,
            'on_sale' => true,
            'sold_out' => false,
            'min_price' => 20.0,
            'max_price' => 20.0,
        ]);
        $this->assertTrue($fixedPaid->isBuyable());
        $this->assertSame('Get Tickets ($20)', $fixedPaid->buttonLabel());

        $free = TicketingData::fromArray([
            'has_tickets' => true,
            'on_sale' => true,
            'sold_out' => false,
            'min_price' => 0.0,
            'max_price' => 0.0,
        ]);
        $this->assertTrue($free->isBuyable());
        $this->assertTrue($free->isFree());
        $this->assertFalse($free->isSlidingScale());
        $this->assertSame('RSVP Free', $free->buttonLabel());

        $slidingZero = TicketingData::fromArray([
            'has_tickets' => true,
            'on_sale' => true,
            'sold_out' => false,
            'min_price' => 0.0,
            'max_price' => 20.0,
        ]);
        $this->assertTrue($slidingZero->isBuyable());
        $this->assertTrue($slidingZero->isSlidingScale());
        $this->assertTrue($slidingZero->isSlidingScaleFromZero());
        $this->assertSame('Register (Free – $20)', $slidingZero->buttonLabel());

        $soldOut = TicketingData::fromArray([
            'has_tickets' => true,
            'on_sale' => true,
            'sold_out' => true,
            'min_price' => 10.0,
            'max_price' => 10.0,
        ]);
        $this->assertFalse($soldOut->isBuyable());
        $this->assertSame('Sold Out', $soldOut->buttonLabel());

        $closed = TicketingData::fromArray([
            'has_tickets' => true,
            'on_sale' => false,
            'sold_out' => false,
            'min_price' => 10.0,
            'max_price' => 10.0,
        ]);
        $this->assertFalse($closed->isBuyable());
        $this->assertSame('Sales Closed', $closed->buttonLabel());
    }

    public function test_active_ticketing_falls_back_to_upcoming_dates(): void
    {
        $eventWithEventTicketing = EventData::fromArray([
            'id' => 1,
            'title' => 'Event 1',
            'ticketing' => [
                'has_tickets' => true,
                'on_sale' => true,
                'min_price' => 25.0,
            ],
        ]);
        $this->assertNotNull($eventWithEventTicketing->activeTicketing());
        $this->assertSame(25.0, $eventWithEventTicketing->activeTicketing()->minPrice);

        $eventWithDateTicketing = EventData::fromArray([
            'id' => 2,
            'title' => 'Event 2',
            'upcoming_dates' => [
                [
                    'id' => 101,
                    'date' => '2026-11-01',
                    'ticketing' => [
                        'has_tickets' => true,
                        'on_sale' => true,
                        'min_price' => 35.0,
                    ],
                ],
            ],
        ]);
        $this->assertNotNull($eventWithDateTicketing->activeTicketing());
        $this->assertSame(35.0, $eventWithDateTicketing->activeTicketing()->minPrice);
    }

    public function test_property_aliases_and_fallbacks(): void
    {
        $event = EventData::fromArray([
            'id' => 50,
            'title' => 'Main Event Title',
            'description' => 'Main Event Description',
        ]);

        $this->assertSame('Main Event Title', $event->title);
        $this->assertSame('Main Event Title', $event->name);

        $date = EventDateData::fromArray([
            'id' => 150,
            'date' => '2026-11-15',
            'event_start' => '2026-11-15 19:00:00',
            'event_end' => '2026-11-15 22:00:00',
            'event' => $event,
        ]);

        // Fallbacks to parent event title/name and description
        $this->assertSame('Main Event Title', $date->title);
        $this->assertSame('Main Event Title', $date->name);
        $this->assertSame('Main Event Description', $date->description);

        // Aliases for start/end time
        $this->assertSame('2026-11-15 19:00:00', $date->startTime);
        $this->assertSame('2026-11-15 19:00:00', $date->eventStart);
        $this->assertSame('2026-11-15 19:00:00', $date->event_start);

        $this->assertSame('2026-11-15 22:00:00', $date->endTime);
        $this->assertSame('2026-11-15 22:00:00', $date->eventEnd);
        $this->assertSame('2026-11-15 22:00:00', $date->event_end);
    }

    public function test_to_ics_generates_rfc_5545_calendar(): void
    {
        $event = EventData::fromArray([
            'id' => 99,
            'title' => 'Community Workshop, Live!',
            'description' => "Join us for an exciting workshop;\nfun for all.",
            'upcoming_dates' => [
                [
                    'id' => 991,
                    'date' => '2026-12-01',
                    'start_time' => '2026-12-01 10:00:00',
                    'end_time' => '2026-12-01 12:00:00',
                    'locations' => [
                        ['name' => 'Studio A, 2nd Floor'],
                    ],
                ],
            ],
        ]);

        $ics = $event->toIcs();

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('VERSION:2.0', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('UID:event-date-991@venuefamily.com', $ics);
        $this->assertStringContainsString('SUMMARY:Community Workshop\, Live!', $ics);
        $this->assertStringContainsString('DESCRIPTION:Join us for an exciting workshop\;\nfun for all.', $ics);
        $this->assertStringContainsString('LOCATION:Studio A\, 2nd Floor', $ics);
        $this->assertStringContainsString('END:VEVENT', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);

        // Also test directly on EventDateData
        $date = $event->upcomingDates[0]->withEvent($event);
        $dateIcs = $date->toIcs();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $dateIcs);
        $this->assertStringContainsString('SUMMARY:Community Workshop\, Live!', $dateIcs);
    }

    public function test_clean_description_and_description_html(): void
    {
        $event = EventData::fromArray([
            'id' => 10,
            'title' => 'Dance Workshop',
            'description' => "Welcome to **Ecstatic Dance**!\n\n\<span style=\"font-family: Arial; font-size: 14pt;\">Come join us.\<\/span\>",
        ]);

        $clean = $event->cleanDescription();
        $this->assertStringNotContainsString('font-family', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringNotContainsString('\<span', $clean);
        $this->assertStringContainsString('Welcome to **Ecstatic Dance**!', $clean);
        $this->assertStringContainsString('Come join us.', $clean);

        $html = $event->descriptionHtml();
        $this->assertStringContainsString('<strong>Ecstatic Dance</strong>', $html);
        $this->assertStringContainsString('<p>', $html);
    }

    public function test_effective_media_and_content_inheritance(): void
    {
        $event = EventData::fromArray([
            'id' => 20,
            'title' => 'Parent Title',
            'subtitle' => 'Parent Subtitle',
            'description' => 'Parent Description',
            'images' => [
                [
                    'id' => 101,
                    'name' => 'parent-page.jpg',
                    'url' => 'https://example.test/parent-page.jpg',
                    'slot' => 'page',
                ],
            ],
        ]);

        $dateWithoutOverrides = EventDateData::fromArray([
            'id' => 201,
            'date' => '2026-10-10',
            'event' => $event,
        ]);

        $this->assertSame(101, $dateWithoutOverrides->effectivePageImage()?->id);
        $this->assertSame('Parent Subtitle', $dateWithoutOverrides->effectiveSubtitle());
        $this->assertSame('Parent Description', $dateWithoutOverrides->effectiveDescription());

        $dateWithOverrides = EventDateData::fromArray([
            'id' => 202,
            'date' => '2026-10-11',
            'subtitle' => 'Specific Date Subtitle',
            'description' => 'Specific Date Description',
            'images' => [
                [
                    'id' => 102,
                    'name' => 'date-page.jpg',
                    'url' => 'https://example.test/date-page.jpg',
                    'slot' => 'page',
                ],
            ],
            'event' => $event,
        ]);

        $this->assertSame(102, $dateWithOverrides->effectivePageImage()?->id);
        $this->assertSame('Specific Date Subtitle', $dateWithOverrides->effectiveSubtitle());
        $this->assertSame('Specific Date Description', $dateWithOverrides->effectiveDescription());
    }

    public function test_schema_org_generation(): void
    {
        $event = EventData::fromArray([
            'id' => 30,
            'title' => 'Full Moon Concert',
            'description' => 'A magical evening of ambient music.',
            'canonical_url' => 'https://the418project.org/events/full-moon-concert',
            'organization' => ['name' => 'The 418 Project'],
            'ticketing' => [
                'has_tickets' => true,
                'min_price' => 25.0,
                'max_price' => 45.0,
                'purchase_url' => 'https://the418project.org/tickets/30',
            ],
            'upcoming_dates' => [
                [
                    'id' => 301,
                    'date' => '2026-11-20',
                    'start_time' => '2026-11-20 19:30:00',
                    'end_time' => '2026-11-20 22:30:00',
                    'locations' => [
                        [
                            'name' => 'Main Hall',
                            'address' => '155 S River St, Santa Cruz, CA',
                        ],
                    ],
                ],
            ],
        ]);

        $schema = $event->toSchemaOrg();
        $this->assertSame('https://schema.org', $schema['@context']);
        $this->assertSame('Event', $schema['@type']);
        $this->assertSame('Full Moon Concert', $schema['name']);
        $this->assertSame('https://the418project.org/events/full-moon-concert', $schema['url']);
        $this->assertSame('https://schema.org/EventScheduled', $schema['eventStatus']);
        $this->assertSame('https://schema.org/OfflineEventAttendanceMode', $schema['eventAttendanceMode']);
        $this->assertSame('Main Hall', $schema['location']['name']);
        $this->assertSame('AggregateOffer', $schema['offers']['@type']);
        $this->assertSame(25.0, $schema['offers']['lowPrice']);
        $this->assertSame(45.0, $schema['offers']['highPrice']);
        $this->assertSame('The 418 Project', $schema['organizer']['name']);

        $json = $event->toSchemaOrgJson();
        $this->assertJson($json);

        $script = $event->toSchemaOrgScript();
        $this->assertStringStartsWith('<script type="application/ld+json">', $script);
        $this->assertStringEndsWith('</script>', $script);

        // Also test on EventDateData
        $date = $event->upcomingDates[0]->withEvent($event);
        $dateSchema = $date->toSchemaOrg();
        $this->assertSame('Full Moon Concert', $dateSchema['name']);
        $this->assertSame('https://the418project.org/events/full-moon-concert#date-301', $dateSchema['url']);
    }

    public function test_event_date_to_array_dual_casing(): void
    {
        $date = EventDateData::fromArray([
            'id' => 501,
            'date' => '2026-12-25',
            'event_start' => '2026-12-25 18:00:00',
            'event_end' => '2026-12-25 21:00:00',
            'door_time' => '17:30:00',
            'is_sold_out' => false,
            'tickets_remaining' => 15,
        ]);

        $arr = $date->toArray();

        // Both camelCase and snake_case exist in toArray
        $this->assertSame('2026-12-25 18:00:00', $arr['event_start']);
        $this->assertSame('2026-12-25 18:00:00', $arr['eventStart']);
        $this->assertSame('2026-12-25 18:00:00', $arr['start_time']);
        $this->assertSame('2026-12-25 18:00:00', $arr['startTime']);

        $this->assertSame('2026-12-25 21:00:00', $arr['event_end']);
        $this->assertSame('2026-12-25 21:00:00', $arr['eventEnd']);
        $this->assertSame('2026-12-25 21:00:00', $arr['end_time']);
        $this->assertSame('2026-12-25 21:00:00', $arr['endTime']);

        $this->assertSame('17:30:00', $arr['door_time']);
        $this->assertSame('17:30:00', $arr['doorTime']);

        $this->assertFalse($arr['is_sold_out']);
        $this->assertFalse($arr['isSoldOut']);

        $this->assertSame(15, $arr['tickets_remaining']);
        $this->assertSame(15, $arr['ticketsRemaining']);

        // Dual access via magic property access
        $this->assertSame('2026-12-25 18:00:00', $date->event_start);
        $this->assertSame('2026-12-25 18:00:00', $date->startTime);
        $this->assertSame('17:30:00', $date->door_time);
        $this->assertSame('17:30:00', $date->doorTime);
    }
}
