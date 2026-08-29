<?php

namespace VenueFamily\Tests;

use PHPUnit\Framework\TestCase;
use VenueFamily\VenueFamilyClient;

class FormsResourceTest extends TestCase
{
    public function test_it_fetches_form_definition(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'forms/waiver' => [
                'id' => 12,
                'title' => 'Liability Waiver',
                'slug' => 'waiver',
                'fields' => [
                    ['id' => 'name', 'type' => 'text', 'label' => 'Full Name'],
                ],
            ],
        ]);

        $form = $client->forms()->find('waiver');

        $this->assertSame(12, $form->id);
        $this->assertSame('Liability Waiver', $form->title);
        $this->assertCount(1, $form->fields);
    }

    public function test_it_submits_form_data(): void
    {
        $client = new VenueFamilyClient('token', 'the-418-project');
        $client->fake([
            'forms/waiver/submit' => [
                'success' => true,
                'submission_id' => 888,
            ],
        ]);

        $response = $client->forms()->submit('waiver', ['name' => 'John Doe']);

        $this->assertTrue($response['success']);
        $this->assertSame(888, $response['submission_id']);
    }
}
