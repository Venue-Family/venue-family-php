<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class SignaturesTest extends TestCase
{
    public function test_it_hydrates_signature_status_and_mechanism(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'signatures/sig_token_abc123' => [
                'data' => [
                    'id' => 99,
                    'token' => 'sig_token_abc123',
                    'status' => 'pending',
                    'mechanism' => 'native',
                    'name' => 'Jane Signer',
                    'email' => 'jane@example.com',
                ],
            ],
        ]);

        $signature = $client->signatures()->find('sig_token_abc123');

        $this->assertSame(99, $signature->id);
        $this->assertSame('sig_token_abc123', $signature->token);
        $this->assertTrue($signature->isPending());
        $this->assertFalse($signature->isSigned());
        $this->assertTrue($signature->isNative());
        $this->assertFalse($signature->isCertified());
        $this->assertSame('Jane Signer', $signature->signerName);
    }

    public function test_it_submits_native_electronic_signature(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'signatures/sig_token_abc123/sign' => [
                'success' => true,
                'status' => 'signed',
            ],
        ]);

        $response = $client->signatures()->signNative(
            'sig_token_abc123',
            'data:image/png;base64,iVBORw0KGgo...',
            'Jane Signer',
            'jane@example.com'
        );

        $this->assertTrue($response['success']);
        $this->assertSame('signed', $response['status']);
    }

    public function test_it_generates_correct_signing_url(): void
    {
        $client = new VenueFamilyClient(baseUrl: 'https://venuefamily.com/api');
        $url = $client->signatures()->getSigningUrl('token_xyz');

        $this->assertSame('https://venuefamily.com/forms/countersign/token_xyz', $url);
    }
}
