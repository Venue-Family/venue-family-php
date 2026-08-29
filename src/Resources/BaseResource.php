<?php

namespace VenueFamily\Resources;

use VenueFamily\VenueFamilyClient;

abstract class BaseResource
{
    public function __construct(protected readonly VenueFamilyClient $client) {}

    protected function orgPath(string $path): string
    {
        $org = $this->client->getOrganization();
        if (empty($org)) {
            throw new \InvalidArgumentException('Organization slug must be set before making organization-scoped requests. Use $client->forOrganization("slug") or configure default organization.');
        }

        $trimmed = ltrim($path, '/');

        return "public/{$org}/{$trimmed}";
    }
}
