<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\Exceptions\VenueFamilyException;
use VenueFamily\Webhooks\WebhookSignature;

class WebhookSignatureTest extends TestCase
{
    public function test_it_verifies_valid_signature_with_prefix(): void
    {
        $payload = json_encode(['event' => 'ticket_purchased', 'id' => 123]);
        $secret = 'super-secret-key';
        $signature = 'sha256='.hash_hmac('sha256', $payload, $secret);

        $this->assertTrue(WebhookSignature::verify($payload, $signature, $secret));
    }

    public function test_it_verifies_valid_signature_without_prefix(): void
    {
        $payload = json_encode(['event' => 'ticket_purchased', 'id' => 123]);
        $secret = 'super-secret-key';
        $signature = hash_hmac('sha256', $payload, $secret);

        $this->assertTrue(WebhookSignature::verify($payload, $signature, $secret));
    }

    public function test_it_rejects_invalid_signature(): void
    {
        $payload = json_encode(['event' => 'ticket_purchased']);
        $secret = 'super-secret-key';
        $signature = 'sha256=invalidhash';

        $this->assertFalse(WebhookSignature::verify($payload, $signature, $secret));
    }

    public function test_it_constructs_verified_event_payload(): void
    {
        $payload = json_encode(['event' => 'form_submitted', 'id' => 456]);
        $secret = 'test-secret';
        $signature = WebhookSignature::sign($payload, $secret);

        $event = WebhookSignature::constructEvent($payload, $signature, $secret);

        $this->assertSame('form_submitted', $event['event']);
        $this->assertSame(456, $event['id']);
    }

    public function test_it_throws_exception_on_invalid_signature_when_constructing_event(): void
    {
        $this->expectException(VenueFamilyException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        $payload = json_encode(['event' => 'form_submitted']);
        WebhookSignature::constructEvent($payload, 'sha256=bad', 'secret');
    }
}
