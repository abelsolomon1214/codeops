<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationAndErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dish_validation_rejects_negative_price_and_unserved_area_with_unified_422(): void
    {
        $restaurant = Restaurant::factory()->create(['area' => 'Bole']);

        $response = $this->postJson('/api/v1/dishes', [
            'name'          => '',
            'restaurant_id' => $restaurant->id,
            'price'         => -500,
            'is_fasting'    => true,
            'area'          => 'Sululta',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'meta' => ['name', 'price', 'area'],
                ],
            ]);
    }

    public function test_partial_patch_on_restaurant_ignores_its_own_slug_and_drops_extra_fields(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name'          => 'Yod Abyssinia',
            'slug'          => 'yod-abyssinia',
            'area'          => 'Bole',
            'phone'         => '0911223344',
            'opening_hours' => '11:00 - 23:00',
        ]);

        $response = $this->patchJson('/api/v1/restaurants/yod-abyssinia', [
            'opening_hours' => '10:00 - 23:30',
            'phone'         => '+251 911-223-344',
            'is_admin'      => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.opening_hours', '10:00 - 23:30')
            ->assertJsonPath('data.slug', 'yod-abyssinia');

        $this->assertDatabaseHas('restaurants', [
            'id'            => $restaurant->id,
            'phone'         => '0911223344',
            'opening_hours' => '10:00 - 23:30',
        ]);
    }

    public function test_order_creation_validates_payload_and_creates_order_lines_atomically(): void
    {
        $restaurant = Restaurant::factory()->create(['area' => 'Bole']);
        $dishOne = Dish::factory()->create(['restaurant_id' => $restaurant->id, 'area' => 'Bole', 'price' => 25000]);
        $dishTwo = Dish::factory()->create(['restaurant_id' => $restaurant->id, 'area' => 'Bole', 'price' => 18000]);

        $response = $this->postJson('/api/v1/orders', [
            'area'  => 'Bole',
            'phone' => '+251 911-234-567',
            'notes' => 'Ring at the gate',
            'lines' => [
                ['dish_id' => $dishOne->id, 'qty' => 2],
                ['dish_id' => $dishTwo->id, 'qty' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertHeader('Location')
            ->assertJsonPath('data.status', 'placed')
            ->assertJsonPath('data.phone', '0911234567')
            ->assertJsonPath('data.subtotal_santim', 68000)
            ->assertJsonPath('data.total_santim', 73000)
            ->assertJsonCount(2, 'data.lines');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_lines', 2);
    }

    public function test_order_validation_rejects_soft_deleted_dish_and_mixed_areas(): void
    {
        $boleRestaurant = Restaurant::factory()->create(['area' => 'Bole']);
        $piassaRestaurant = Restaurant::factory()->create(['area' => 'Piassa']);

        $boleDish = Dish::factory()->create(['restaurant_id' => $boleRestaurant->id, 'area' => 'Bole']);
        $piassaDish = Dish::factory()->create(['restaurant_id' => $piassaRestaurant->id, 'area' => 'Piassa']);
        $withdrawnDish = Dish::factory()->create(['restaurant_id' => $boleRestaurant->id, 'area' => 'Bole']);
        $withdrawnDish->delete();

        $this->postJson('/api/v1/orders', [
            'area'  => 'Bole',
            'phone' => '0911234567',
            'lines' => [
                ['dish_id' => $withdrawnDish->id, 'qty' => 1],
            ],
        ])->assertStatus(422)
          ->assertJsonPath('error.code', 'validation_failed');

        $this->postJson('/api/v1/orders', [
            'area'  => 'Bole',
            'phone' => '0911234567',
            'lines' => [
                ['dish_id' => $boleDish->id, 'qty' => 1],
                ['dish_id' => $piassaDish->id, 'qty' => 1],
            ],
        ])->assertStatus(422)
          ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_illegal_order_status_transition_returns_409_conflict(): void
    {
        $restaurant = Restaurant::factory()->create(['area' => 'Bole']);
        $dish = Dish::factory()->create(['restaurant_id' => $restaurant->id, 'area' => 'Bole', 'price' => 20000]);

        $created = $this->postJson('/api/v1/orders', [
            'area'  => 'Bole',
            'phone' => '0911234567',
            'lines' => [
                ['dish_id' => $dish->id, 'qty' => 1],
            ],
        ])->assertCreated();

        $orderId = $created->json('data.id');

        $this->patchJson("/api/v1/orders/{$orderId}/status", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->patchJson("/api/v1/orders/{$orderId}/status", ['status' => 'delivered'])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'illegal_transition')
            ->assertJsonPath('error.meta.from', 'cancelled')
            ->assertJsonPath('error.meta.to', 'delivered');
    }

    public function test_not_found_and_method_not_allowed_share_unified_error_envelope(): void
    {
        $this->getJson('/api/v1/dishes/999999')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found')
            ->assertJsonStructure(['error' => ['code', 'message', 'meta']]);

        $this->getJson('/api/v1/non-existent-endpoint')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'route_not_found')
            ->assertJsonStructure(['error' => ['code', 'message', 'meta']]);

        $this->patchJson('/api/v1/orders')
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'method_not_allowed')
            ->assertJsonStructure(['error' => ['code', 'message', 'meta']]);
    }
}
