# Venue Family PHP & Laravel SDK

Official PHP and Laravel client library for integrating with the [Venue Family](https://venuefamily.com) API, ticketing, schedules, and dynamic forms.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/venue-family/sdk.svg?style=flat-square)](https://packagist.org/packages/venue-family/sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/venue-family/sdk.svg?style=flat-square)](https://packagist.org/packages/venue-family/sdk)
[![License](https://img.shields.io/packagist/l/venue-family/sdk.svg?style=flat-square)](https://packagist.org/packages/venue-family/sdk)

---

## Requirements

- PHP 8.2 or higher
- Guzzle HTTP 7.5+
- (Optional) Laravel 10, 11, or 12

---

## Installation

Install the package via Composer:

```bash
composer require venue-family/sdk
```

In Laravel, the package will automatically register its `VenueFamilyServiceProvider` and `VenueFamily` facade.

### Publish Configuration (Laravel)

```bash
php artisan vendor:publish --tag=venue-family-config
```

Configure your environment variables in `.env`:

```dotenv
VENUE_FAMILY_BASE_URL=https://venuefamily.com/api
VENUE_FAMILY_API_KEY=your_organization_api_token_here
VENUE_FAMILY_ORGANIZATION=the-418-project
VENUE_FAMILY_WEBHOOK_SECRET=your_webhook_signing_secret
```

---

## Usage

### In Laravel (Using the Facade)

```php
use VenueFamily\Laravel\Facades\VenueFamily;

// 1. Fetch upcoming events for your configured organization
$events = VenueFamily::events()->upcoming(
    tags: ['dance', 'workshop'],
    location: 'main-theater'
);

foreach ($events as $event) {
    echo $event->title;
    echo $event->coverImageUrl;
    
    foreach ($event->upcomingDates as $date) {
        echo "Date: {$date->date} at {$date->startTime}";
        echo "Tickets Remaining: {$date->ticketsRemaining}";
    }
}

// 2. Query for a different organization on the fly
$otherOrgEvents = VenueFamily::forOrganization('other-venue')
    ->events()
    ->upcoming();

// 3. Search events by keyword
$results = VenueFamily::events()->search('ecstatic');

// 4. Retrieve single event details
$event = VenueFamily::events()->find('community-dance');

// 5. Retrieve locations & spaces
$locations = VenueFamily::locations()->all(['city' => 'Santa Cruz']);

// 6. Submit a dynamic form or waiver
$response = VenueFamily::forms()->submit('liability-waiver', [
    'f_name' => 'Jane Doe',
    'f_emergency_phone' => '831-555-0199',
    'f_sig' => 'data:image/png;base64,...',
]);
```

### In Pure PHP (Vanilla / Non-Laravel)

```php
use VenueFamily\VenueFamilyClient;

$client = new VenueFamilyClient(
    apiKey: 'your_api_key_here',
    organization: 'the-418-project',
    baseUrl: 'https://venuefamily.com/api'
);

$events = $client->events()->upcoming();
$locations = $client->locations()->all();
```

---

## Webhook & Postback Verification

Venue Family postbacks and incoming webhook events are cryptographically signed using HMAC-SHA256.

### Laravel Middleware

Protect your webhook route using the bundled middleware:

```php
use Illuminate\Support\Facades\Route;
use VenueFamily\Laravel\Middleware\VerifyVenueFamilyWebhookSignature;

Route::post('/webhooks/venue-family', function (Request $request) {
    $payload = $request->all();
    // Process verified webhook payload safely...
    return response()->json(['status' => 'received']);
})->middleware(VerifyVenueFamilyWebhookSignature::class);
```

### Manual Verification (Pure PHP)

```php
use VenueFamily\Webhooks\WebhookSignature;

$payload = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_X_VF_SIGNATURE'] ?? '';
$secret = 'your_webhook_signing_secret';

if (WebhookSignature::verify($payload, $signatureHeader, $secret)) {
    $event = WebhookSignature::constructEvent($payload, $signatureHeader, $secret);
    // Process valid event...
} else {
    http_response_code(401);
    exit('Invalid signature');
}
```

---

## Testing & Mocking in Your Application

The SDK provides a testing fake so your application tests don't make real HTTP calls:

```php
use VenueFamily\Laravel\Facades\VenueFamily;

test('it syncs upcoming events from venue family', function () {
    // 1. Fake the Venue Family client with custom fixture data
    VenueFamily::fake([
        'events/upcoming' => [
            'data' => [
                [
                    'id' => 101,
                    'title' => 'Simulated Show',
                    'slug' => 'simulated-show',
                    'type' => 'performance',
                    'upcoming_dates' => [
                        [
                            'id' => 201,
                            'date' => '2026-10-01',
                            'start_time' => '20:00',
                            'tickets_remaining' => 50,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    // 2. Run your application code
    $this->artisan('sync:events')->assertSuccessful();

    // 3. Assert requests were made as expected
    VenueFamily::assertSent(function ($method, $url, $options) {
        return $method === 'GET' && str_contains($url, 'events/upcoming');
    });
});
```

---

## License

The Venue Family PHP SDK is open-source software licensed under the [MIT license](LICENSE).
