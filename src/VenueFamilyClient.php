<?php

namespace VenueFamily;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use VenueFamily\Exceptions\AuthenticationException;
use VenueFamily\Exceptions\NotFoundException;
use VenueFamily\Exceptions\RateLimitException;
use VenueFamily\Exceptions\ValidationException;
use VenueFamily\Exceptions\VenueFamilyException;
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

class VenueFamilyClient
{
    private ?ClientInterface $httpClient = null;

    private ?VenueFamilyFake $fake = null;

    public function __construct(
        private ?string $apiKey = null,
        private ?string $organization = null,
        private string $baseUrl = 'https://venuefamily.com/api',
        private array $config = []
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public static function make(
        ?string $apiKey = null,
        ?string $organization = null,
        string $baseUrl = 'https://venuefamily.com/api',
        array $config = []
    ): self {
        return new self($apiKey, $organization, $baseUrl, $config);
    }

    /**
     * Create a new client instance scoped to a specific organization.
     */
    public function forOrganization(string $organization): self
    {
        $clone = clone $this;
        $clone->organization = $organization;

        return $clone;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    // --- Resources ---

    public function auth(): AuthResource
    {
        return new AuthResource($this);
    }

    public function users(): UsersResource
    {
        return new UsersResource($this);
    }

    public function roles(): RolesResource
    {
        return new RolesResource($this);
    }

    public function signatures(): SignaturesResource
    {
        return new SignaturesResource($this);
    }

    public function events(): EventsResource
    {
        return new EventsResource($this);
    }

    public function locations(): LocationsResource
    {
        return new LocationsResource($this);
    }

    public function forms(): FormsResource
    {
        return new FormsResource($this);
    }

    public function reviews(): ReviewsResource
    {
        return new ReviewsResource($this);
    }

    public function conversions(): ConversionsResource
    {
        return new ConversionsResource($this);
    }

    public function interests(): InterestsResource
    {
        return new InterestsResource($this);
    }

    // --- Testing Harness ---

    public function fake(?array $responses = null): VenueFamilyFake
    {
        $this->fake = new VenueFamilyFake($responses ?? []);

        return $this->fake;
    }

    public function isFaked(): bool
    {
        return $this->fake !== null;
    }

    public function getFake(): ?VenueFamilyFake
    {
        return $this->fake;
    }

    // --- HTTP Methods ---

    public function get(string $path, array $query = []): array
    {
        $options = [];
        if (! empty($query)) {
            $options['query'] = $query;
        }

        return $this->sendRequest('GET', $path, $options);
    }

    public function post(string $path, array $data = []): array
    {
        return $this->sendRequest('POST', $path, ['json' => $data]);
    }

    public function put(string $path, array $data = []): array
    {
        return $this->sendRequest('PUT', $path, ['json' => $data]);
    }

    public function patch(string $path, array $data = []): array
    {
        return $this->sendRequest('PATCH', $path, ['json' => $data]);
    }

    public function delete(string $path, array $data = []): array
    {
        $options = [];
        if (! empty($data)) {
            $options['json'] = $data;
        }

        return $this->sendRequest('DELETE', $path, $options);
    }

    public function sendRawRequest(string $method, string $path, array $options = []): array
    {
        return $this->sendRequest($method, $path, $options);
    }

    private function sendRequest(string $method, string $path, array $options = []): array
    {
        $uri = ltrim($path, '/');
        $fullUrl = "{$this->baseUrl}/{$uri}";

        if ($this->fake !== null) {
            return $this->fake->recordAndRespond($method, $fullUrl, $options);
        }

        $client = $this->getHttpClient();

        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer '.$this->apiKey;
            $headers['X-API-Key'] = $this->apiKey;
        }

        if (isset($options['headers'])) {
            $headers = array_merge($headers, $options['headers']);
            unset($options['headers']);
        }

        $options['headers'] = $headers;

        try {
            $response = $client->request($method, $fullUrl, $options);

            return $this->handleResponse($response);
        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $body = $this->parseResponseBody($e->getResponse());
            throw new VenueFamilyException(
                'Venue Family server error: '.$e->getMessage(),
                $e->getCode(),
                $body
            );
        } catch (GuzzleException $e) {
            throw new VenueFamilyException(
                'Venue Family request failed: '.$e->getMessage(),
                (int) $e->getCode()
            );
        }
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if (empty($body)) {
            return [];
        }

        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return ['raw' => $body];
        }
    }

    private function handleClientException(ClientException $e): never
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = $this->parseResponseBody($e->getResponse());
        $message = $body['message'] ?? $body['error'] ?? $e->getMessage();

        match ($statusCode) {
            401, 403 => throw new AuthenticationException($message, $statusCode, $body),
            404 => throw new NotFoundException($message, $statusCode, $body),
            422 => throw new ValidationException($message, $body['errors'] ?? [], $body),
            429 => throw new RateLimitException(
                $message,
                $e->getResponse()->hasHeader('Retry-After') ? (int) $e->getResponse()->getHeaderLine('Retry-After') : null,
                $body
            ),
            default => throw new VenueFamilyException($message, $statusCode, $body),
        };
    }

    private function parseResponseBody(?ResponseInterface $response): ?array
    {
        if (! $response) {
            return null;
        }

        try {
            return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
    }

    private function getHttpClient(): ClientInterface
    {
        if ($this->httpClient === null) {
            $this->httpClient = new GuzzleClient([
                'timeout' => $this->config['timeout'] ?? 30.0,
                'http_errors' => true,
            ]);
        }

        return $this->httpClient;
    }

    public function setHttpClient(ClientInterface $client): self
    {
        $this->httpClient = $client;

        return $this;
    }
}
