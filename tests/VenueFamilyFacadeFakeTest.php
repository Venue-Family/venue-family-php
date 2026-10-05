<?php

namespace VenueFamily\Tests;

use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use VenueFamily\Laravel\Facades\VenueFamily;

/**
 * `VenueFamily::fake()` returned a fake that recorded nothing.
 *
 * The facade built one VenueFamilyFake, swapped in a fresh client, then asked
 * that client to fake() — which builds a SECOND fake and records every request
 * on it — and returned the first. So `$fake = VenueFamily::fake(); …;
 * $fake->assertSent(...)` failed for a request that was sent, and
 * `$fake->assertNothingSent()` passed for one that was: an assertion that can
 * only ever say "nothing happened" protects nothing.
 */
class VenueFamilyFacadeFakeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstances();
        // The two things a facade asks of the application, without pulling the
        // whole container in as a dependency of an SDK that only suggests Laravel.
        Facade::setFacadeApplication(new class implements \ArrayAccess
        {
            private array $bound = [];

            public function instance(string $name, mixed $instance): void
            {
                $this->bound[$name] = $instance;
            }

            public function offsetExists(mixed $offset): bool
            {
                return isset($this->bound[$offset]);
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $this->bound[$offset];
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                $this->bound[$offset] = $value;
            }

            public function offsetUnset(mixed $offset): void
            {
                unset($this->bound[$offset]);
            }
        });
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);

        parent::tearDown();
    }

    public function test_the_fake_the_facade_returns_is_the_one_that_records(): void
    {
        $fake = VenueFamily::fake([
            'events/upcoming' => ['data' => [['id' => 1, 'title' => 'Test Event']]],
        ]);

        VenueFamily::events()->upcoming();

        $fake->assertSentCount(1);
        $fake->assertSent(fn ($method, $url) => $method === 'GET' && str_contains($url, 'events/upcoming'));
        $this->assertSame($fake, VenueFamily::getFake());
    }

    public function test_assert_nothing_sent_fails_once_a_request_has_been_made(): void
    {
        $fake = VenueFamily::fake(['events/upcoming' => ['data' => []]]);

        VenueFamily::events()->upcoming();

        $this->expectException(AssertionFailedError::class);

        $fake->assertNothingSent();
    }
}
