<?php

namespace Tests\Feature\Order;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrieve_shipping_notes()
    {
        $response = $this->get('/api/v1/orders/shipping-notes');

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'data' => [
                        'options' => [
                            'delivery_note',
                            'pickup_note',
                        ]
                    ],
                ]);
    }

    public function test_update_shipping_notes()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $response = $this->patch('/api/v1/orders/shipping-notes/update', [
            'delivery_note' => 'This is a test delivery note',
            'pickup_note' => 'This is a test pickup note',
        ]);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertDatabaseHas('shipping_options', [
            'delivery_note' => 'This is a test delivery note',
            'pickup_note' => 'This is a test pickup note',
        ]);
    }

    public function test_update_only_delivery_note()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $response = $this->patch('/api/v1/orders/shipping-notes/update', [
            'delivery_note' => 'This is a test delivery note',
        ]);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertDatabaseHas('shipping_options', [
            'delivery_note' => 'This is a test delivery note',
        ]);
    }
}