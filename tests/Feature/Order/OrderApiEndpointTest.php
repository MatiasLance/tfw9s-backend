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

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Sanctum::actingAs($admin);

        return $admin;
    }

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
                            'delivery_charge',
                            'pickup_charge',
                        ],
                        'charges' => [
                            'delivery',
                            'pickup',
                        ],
                    ],
                ])
                ->assertJsonPath('data.charges.delivery', 1000)
                ->assertJsonPath('data.charges.pickup', 0);
    }

    public function test_update_shipping_notes()
    {
        $this->actingAsAdmin();

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
        $this->actingAsAdmin();

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

    public function test_admin_can_update_shipping_charges()
    {
        $this->actingAsAdmin();

        $response = $this->patch('/api/v1/orders/shipping-settings', [
            'delivery_charge' => 1250,
            'pickup_charge' => 250,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.charges.delivery', 1250)
            ->assertJsonPath('data.charges.pickup', 250);

        $this->assertDatabaseHas('shipping_options', [
            'delivery_charge' => 1250,
            'pickup_charge' => 250,
        ]);
    }

    public function test_shipping_settings_require_an_admin_role()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->get('/api/v1/orders/shipping-settings')
            ->assertStatus(403);

        $this->patch('/api/v1/orders/shipping-settings', [
            'delivery_charge' => 1250,
        ])->assertStatus(403);

        $this->assertDatabaseHas('shipping_options', [
            'delivery_charge' => 1000,
            'pickup_charge' => 0,
        ]);
    }

    public function test_shipping_settings_require_authentication()
    {
        $this->getJson('/api/v1/orders/shipping-settings')
            ->assertStatus(401);

        $this->patchJson('/api/v1/orders/shipping-settings', [
            'delivery_charge' => 1250,
        ])->assertStatus(401);
    }

    public function test_admin_can_retrieve_shipping_settings()
    {
        $this->actingAsAdmin();

        $this->get('/api/v1/orders/shipping-settings')
            ->assertStatus(200)
            ->assertJsonPath('data.charges.delivery', 1000)
            ->assertJsonPath('data.charges.pickup', 0);
    }
}
