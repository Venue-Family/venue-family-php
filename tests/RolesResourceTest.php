<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class RolesResourceTest extends TestCase
{
    public function test_it_hydrates_roles_with_scopes_and_abilities(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'roles' => [
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Partner / Resident Artist',
                        'slug' => 'partner',
                        'scope' => 'own',
                        'abilities' => ['events.manage_own'],
                    ],
                    [
                        'id' => 2,
                        'name' => 'Venue Manager',
                        'slug' => 'manager',
                        'scope' => 'all',
                        'abilities' => ['events.manage_all', 'finance.view'],
                    ],
                ],
            ],
        ]);

        $roles = $client->roles()->all();

        $this->assertCount(2, $roles);
        $partner = $roles[0];
        $this->assertSame('partner', $partner->slug);
        $this->assertTrue($partner->isOwnScope());
        $this->assertFalse($partner->isAllScope());

        $manager = $roles[1];
        $this->assertSame('manager', $manager->slug);
        $this->assertTrue($manager->isAllScope());
        $this->assertFalse($manager->isOwnScope());
    }

    public function test_it_queries_volunteer_roles_and_opportunities(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'volunteer-opportunities' => [
                'data' => [
                    [
                        'id' => 10,
                        'name' => 'FOH Box Office Usher',
                        'date' => '2026-09-15',
                        'start_time' => '18:00',
                        'spots_needed' => 2,
                        'spots_filled' => 1,
                    ],
                ],
            ],
        ]);

        $opps = $client->users()->volunteerOpportunities();

        $this->assertCount(1, $opps);
        $this->assertSame('FOH Box Office Usher', $opps[0]->name);
        $this->assertSame(2, $opps[0]->spotsNeeded);
        $this->assertSame(1, $opps[0]->spotsFilled);
    }
}
