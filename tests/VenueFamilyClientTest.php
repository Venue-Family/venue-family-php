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

    public function test_with_token_and_with_api_key_returns_cloned_instance_with_updated_token(): void
    {
        $client = VenueFamilyClient::make(
            apiKey: 'original-token',
            organization: 'the-418-project',
            baseUrl: 'https://venuefamily.com/api'
        );

        $withToken = $client->withToken('bearer-user-token');
        $withApiKey = $client->withApiKey('new-api-key');

        $this->assertSame('original-token', $client->getApiKey());
        $this->assertSame('bearer-user-token', $withToken->getApiKey());
        $this->assertSame('the-418-project', $withToken->getOrganization());
        $this->assertSame('https://venuefamily.com/api', $withToken->getBaseUrl());

        $this->assertSame('original-token', $client->getApiKey());
        $this->assertSame('new-api-key', $withApiKey->getApiKey());
    }

    public function test_it_caches_get_requests_when_cache_callbacks_are_configured(): void
    {
        $client = VenueFamilyClient::make(
            apiKey: 'token',
            organization: 'the-418-project'
        );

        $cacheStore = [];
        $client = $client->withCache(
            get: function (string $key) use (&$cacheStore) {
                return $cacheStore[$key] ?? null;
            },
            put: function (string $key, array $data, int $ttl) use (&$cacheStore) {
                $cacheStore[$key] = $data;
            },
            flush: function () use (&$cacheStore) {
                $cacheStore = [];
            },
            ttl: 300
        );

        $client->fake([
            'test-endpoint' => ['data' => ['hello' => 'world']],
        ]);

        $first = $client->get('test-endpoint');
        $this->assertSame(['hello' => 'world'], $first['data']);
        $this->assertNotEmpty($cacheStore);

        // Next call should read from cacheStore even if fake is changed
        $client->fake([
            'test-endpoint' => ['data' => ['hello' => 'different']],
        ]);

        $cached = $client->get('test-endpoint');
        $this->assertSame(['hello' => 'world'], $cached['data']);

        // Flushing cache clears it
        $client->flushCache();
        $this->assertEmpty($cacheStore);

        $afterFlush = $client->get('test-endpoint');
        $this->assertSame(['hello' => 'different'], $afterFlush['data']);
    }
}
