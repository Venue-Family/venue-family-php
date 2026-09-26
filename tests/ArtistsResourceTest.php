<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class ArtistsResourceTest extends TestCase
{
    private VenueFamilyClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = VenueFamilyClient::make(
            apiKey: 'token',
            organization: 'the-418-project'
        );
    }

    public function test_it_fetches_and_hydrates_artists(): void
    {
        $this->client->fake([
            'public/the-418-project/marketplace/artists' => [
                'data' => [
                    [
                        'id' => 7,
                        'artist_name' => 'Maya Lin',
                        'bio' => 'Contemporary dancer and choreographer \<span style="color:red">\</span>.',
                        'profile_image' => 'https://example.test/maya.jpg',
                        'location' => 'Santa Cruz, CA',
                        'travel_distance' => 50,
                        'website' => 'https://mayadance.test',
                        'interests' => [['id' => 1, 'name' => 'Dance']],
                        'portfolio_images' => ['https://example.test/portfolio1.jpg'],
                        'social_media' => ['instagram' => '@mayadance'],
                        'links' => [['name' => 'Instagram', 'url' => 'https://instagram.com/mayadance']],
                    ],
                ],
            ],
        ]);

        $artists = $this->client->artists()->all(['search' => 'Maya']);

        $this->assertCount(1, $artists);
        $artist = $artists[0];
        $this->assertSame(7, $artist->id);
        $this->assertSame('Maya Lin', $artist->artistName);
        $this->assertSame('Maya Lin', $artist->name);
        $this->assertSame('Contemporary dancer and choreographer .', $artist->cleanBio());
        $this->assertSame('https://example.test/maya.jpg', $artist->profileImage);
        $this->assertSame('Santa Cruz, CA', $artist->location);
        $this->assertSame(50, $artist->travelDistance);
        $this->assertCount(1, $artist->portfolioImages);
        $this->assertSame('@mayadance', $artist->socialMedia['instagram']);

        // Test marketplace alias
        $viaMarketplace = $this->client->marketplace()->all();
        $this->assertCount(1, $viaMarketplace);
        $this->assertSame(7, $viaMarketplace[0]->id);
    }

    public function test_it_finds_single_artist(): void
    {
        $this->client->fake([
            'public/the-418-project/marketplace/artists/maya-lin' => [
                'data' => [
                    'id' => 7,
                    'artist_name' => 'Maya Lin',
                    'bio' => 'Choreographer',
                ],
            ],
        ]);

        $artist = $this->client->artists()->find('maya-lin');

        $this->assertSame(7, $artist->id);
        $this->assertSame('Maya Lin', $artist->artistName);
    }
}
