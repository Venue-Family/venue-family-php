<?php

namespace VenueFamily\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

class VenueFamilyFake
{
    /**
     * @var array<int, array{method: string, url: string, options: array}>
     */
    private array $recorded = [];

    public function __construct(private array $responses = []) {}

    /**
     * Record a request and return a mocked response.
     */
    public function recordAndRespond(string $method, string $url, array $options): array
    {
        $this->recorded[] = [
            'method' => strtoupper($method),
            'url' => $url,
            'options' => $options,
        ];

        // Check if there is a matching custom response
        foreach ($this->responses as $key => $response) {
            if (is_string($key) && (str_contains($url, $key) || fnmatch($key, $url))) {
                if (is_callable($response)) {
                    return $response($method, $url, $options);
                }

                return is_array($response) ? $response : ['data' => $response];
            }
        }

        // Return empty structure or default data
        return ['data' => []];
    }

    /**
     * Assert that a request matching a given callback was sent.
     *
     * @param  callable(string $method, string $url, array $options): bool  $callback
     */
    public function assertSent(callable $callback): void
    {
        $matching = array_filter($this->recorded, function ($req) use ($callback) {
            return $callback($req['method'], $req['url'], $req['options']);
        });

        PHPUnit::assertTrue(
            count($matching) > 0,
            'Failed asserting that a matching request to Venue Family was sent.'
        );
    }

    /**
     * Assert that a request matching a given callback was NOT sent.
     *
     * @param  callable(string $method, string $url, array $options): bool  $callback
     */
    public function assertNotSent(callable $callback): void
    {
        $matching = array_filter($this->recorded, function ($req) use ($callback) {
            return $callback($req['method'], $req['url'], $req['options']);
        });

        PHPUnit::assertCount(
            0,
            $matching,
            'Failed asserting that no matching request to Venue Family was sent.'
        );
    }

    /**
     * Assert the total number of requests sent to Venue Family.
     */
    public function assertSentCount(int $count): void
    {
        PHPUnit::assertCount(
            $count,
            $this->recorded,
            "Expected {$count} requests to Venue Family, but found ".count($this->recorded).'.'
        );
    }

    /**
     * Assert that no requests were sent to Venue Family.
     */
    public function assertNothingSent(): void
    {
        $this->assertSentCount(0);
    }

    /**
     * Retrieve all recorded requests.
     *
     * @return array<int, array{method: string, url: string, options: array}>
     */
    public function recorded(): array
    {
        return $this->recorded;
    }
}
