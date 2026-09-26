<?php

namespace VenueFamily\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use VenueFamily\Resources\AuthResource;
use VenueFamily\Resources\ConversionsResource;
use VenueFamily\Resources\EventsResource;
use VenueFamily\Resources\FormsResource;
use VenueFamily\Resources\InterestsResource;
use VenueFamily\Resources\LocationsResource;
use VenueFamily\Resources\ReviewsResource;
use VenueFamily\Resources\RolesResource;
use VenueFamily\Resources\SignaturesResource;
use VenueFamily\Resources\UsersResource;
use VenueFamily\Testing\VenueFamilyFake;
use VenueFamily\VenueFamilyClient;

/**
 * @method static VenueFamilyClient forOrganization(string $organization)
 * @method static ?string getOrganization()
 * @method static AuthResource auth()
 * @method static UsersResource users()
 * @method static RolesResource roles()
 * @method static SignaturesResource signatures()
 * @method static EventsResource events()
 * @method static LocationsResource locations()
 * @method static FormsResource forms()
 * @method static ReviewsResource reviews()
 * @method static ConversionsResource conversions()
 * @method static InterestsResource interests()
 * @method static \VenueFamily\Resources\EmbedResource embed()
 * @method static \VenueFamily\Resources\ArtistsResource artists()
 * @method static \VenueFamily\Resources\MarketplaceResource marketplace()
 * @method static \VenueFamily\Resources\VolunteeringResource volunteering()
 * @method static VenueFamilyFake fake(?array $responses = null)
 * @method static bool isFaked()
 * @method static ?VenueFamilyFake getFake()
 * @method static array get(string $path, array $query = [])
 * @method static array post(string $path, array $data = [])
 * @method static array put(string $path, array $data = [])
 * @method static array patch(string $path, array $data = [])
 * @method static array delete(string $path, array $data = [])
 * @method static void assertSent(callable $callback)
 * @method static void assertNotSent(callable $callback)
 * @method static void assertSentCount(int $count)
 * @method static void assertNothingSent()
 *
 * @see VenueFamilyClient
 */
class VenueFamily extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'venue-family';
    }

    /**
     * Replace the bound instance with a fake for testing.
     */
    public static function fake(?array $responses = null): VenueFamilyFake
    {
        $fake = new VenueFamilyFake($responses ?? []);
        static::swap(new VenueFamilyClient(
            apiKey: 'test-key',
            organization: 'test-org',
            baseUrl: 'https://venuefamily.test/api'
        ));

        static::getFacadeRoot()->fake($responses ?? []);

        return $fake;
    }

    /**
     * Assert that a request was sent matching a callback.
     *
     * @param  callable(string $method, string $url, array $options): bool  $callback
     */
    public static function assertSent(callable $callback): void
    {
        static::getFacadeRoot()->getFake()?->assertSent($callback);
    }

    /**
     * Assert that no request matching a callback was sent.
     *
     * @param  callable(string $method, string $url, array $options): bool  $callback
     */
    public static function assertNotSent(callable $callback): void
    {
        static::getFacadeRoot()->getFake()?->assertNotSent($callback);
    }

    /**
     * Assert the total number of requests sent.
     */
    public static function assertSentCount(int $count): void
    {
        static::getFacadeRoot()->getFake()?->assertSentCount($count);
    }

    /**
     * Assert that no requests were sent.
     */
    public static function assertNothingSent(): void
    {
        static::getFacadeRoot()->getFake()?->assertNothingSent();
    }
}
