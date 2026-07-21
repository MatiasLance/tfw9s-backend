<?php

namespace Tests\Feature\Order;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_hidden_items_before_contacting_gateway(): void
    {
        $item = Item::factory()->create(['is_active' => false]);

        $this->postJson('/api/v1/orders/checkout', [
            'items' => [[
                'id' => $item->id,
                'quantity' => 1,
            ]],
            'payment_method' => 'stripe',
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'One or more items are no longer available. Please refresh your cart.');
    }

    public function test_shipping_calculation_rejects_hidden_items(): void
    {
        $item = Item::factory()->create(['is_active' => false]);

        $this->postJson('/api/v1/orders/calculation', [
            'items' => [[
                'id' => $item->id,
                'quantity' => 1,
            ]],
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'One or more items are no longer available. Please refresh your cart.');
    }

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
