<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class AuthResourceTest extends TestCase
{
    public function test_it_logs_in_and_returns_tokens_and_user(): void
    {
        $client = new VenueFamilyClient;
        $client->fake([
            'auth/login' => [
                'token' => 'sanctum_bearer_token_123',
                'token_type' => 'Bearer',
                'user' => [
                    'id' => 1,
                    'name' => 'Alice Artist',
                    'email' => 'alice@example.com',
                    'is_staff' => false,
                ],
            ],
        ]);

        $response = $client->auth()->login('alice@example.com', 'password', 'the-418-project');

        $this->assertSame('sanctum_bearer_token_123', $response['token']);
        $this->assertSame('Alice Artist', $response['user']['name']);
    }

    public function test_it_retrieves_authenticated_user_profile(): void
    {
        $client = new VenueFamilyClient('sanctum_token');
        $client->fake([
            'auth/user' => [
                'user' => [
                    'id' => 42,
                    'name' => 'Bob Manager',
                    'email' => 'bob@example.com',
                    'is_staff' => true,
                    'organizations' => [
                        ['id' => 1, 'name' => 'The 418 Project', 'slug' => 'the-418-project'],
                    ],
                ],
            ],
        ]);

        $user = $client->auth()->user();

        $this->assertSame(42, $user->id);
        $this->assertSame('Bob Manager', $user->name);
        $this->assertTrue($user->isStaff);
        $this->assertCount(1, $user->organizations);
    }

    public function test_it_updates_profile_and_returns_hydrated_user(): void
    {
        $client = new VenueFamilyClient('sanctum_token');
        $client->fake([
            'auth/profile' => [
                'user' => [
                    'id' => 42,
                    'name' => 'Bob Updated',
                    'email' => 'bob@example.com',
                    'artist_name' => 'DJ Bobby',
                ],
            ],
        ]);

        $user = $client->auth()->updateProfile([
            'name' => 'Bob Updated',
            'artist_name' => 'DJ Bobby',
        ]);

        $this->assertSame('Bob Updated', $user->name);
        $this->assertSame('DJ Bobby', $user->artistName);
    }
}
