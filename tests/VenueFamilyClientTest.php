<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class VenueFamilyClientTest extends TestCase
{
    public function test_it_initializes_with_correct_configuration(): void
    {
        $client = new VenueFamilyClient(
            apiKey: 'test-token',
            organization: 'the-418-project',
            baseUrl: 'https://venuefamily.com/api'
        );

        $this->assertSame('test-token', $client->getApiKey());
        $this->assertSame('the-418-project', $client->getOrganization());
        $this->assertSame('https://venuefamily.com/api', $client->getBaseUrl());
    }

    public function test_it_can_switch_organization_scope(): void
    {
        $client = VenueFamilyClient::make(
            apiKey: 'test-token',
            organization: 'the-418-project'
        );

        $scoped = $client->forOrganization('other-venue');

        $this->assertSame('the-418-project', $client->getOrganization());
        $this->assertSame('other-venue', $scoped->getOrganization());
    }

    public function test_it_trims_trailing_slashes_from_base_url(): void
    {
        $client = new VenueFamilyClient(
            baseUrl: 'https://venuefamily.com/api///'
        );

        $this->assertSame('https://venuefamily.com/api', $client->getBaseUrl());
    }
}
