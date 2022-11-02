<?php

namespace Tests\Feature\Item;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing all categories
     * 
     * @return void
     */
    public function test_list_all_categories()
    {
        $response = $this->get('/api/v1/categories/');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'status',
                    'title',
                    'data' => [
                        'categories' => [
                            '*' => [
                                'id',
                                'name',
                                'children'
                            ]
                        ],
                    ],
                ]
            );
    }

    public function test_create_new_category()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->post('api/v1/categories/', [
            'name' => 'New Test Category'
        ]);

        $response
                ->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertDatabaseHas(Category::class, [
            'name' => 'New Test Category'
        ]);
    }

    public function test_update_category()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $parentCategory = Category::factory()->create([
            'name' => 'Parent Category',
        ]);

        $category = Category::factory()->create([
            'name' => 'Test Category',
            'parent_id' => null
        ]);

        $this->assertDatabaseHas(Category::class, [
            'name' => 'Test Category',
            'parent_id' => null
        ]);

        $response = $this->patch('/api/v1/categories/' . $category->id, [
            'name' => 'Updated Category',
            'parentId' => strval($parentCategory->id),
        ]);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertDatabaseMissing(Category::class, [
            'name' => 'Test Category',
            'parent_id' => null
        ]);

        $this->assertDatabaseHas(Category::class, [
            'name' => 'Updated Category',
            'parent_id' => $parentCategory->id
        ]);
    }

    public function test_move_categories()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $parentCategory = Category::factory()->create([
            'name' => 'Parent Category',
        ]);

        $childCategory_1 = Category::factory()->create([
            'name' => 'Child Category 1',
            'parent_id' => null
        ]);

        $childCategory_2 = Category::factory()->create([
            'name' => 'Child Category 2',
            'parent_id' => null
        ]);

        $response = $this->post('/api/v1/categories/move', [
            'categories' => [
                strval($childCategory_1->id),
                strval($childCategory_2->id),
            ],
            'target' => strval($parentCategory->id)
        ]);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);
        
        $this->assertDatabaseHas(Category::class, [
            'id' => $childCategory_1->id,
            'parent_id' => $parentCategory->id,
        ]);
        
        $this->assertDatabaseHas(Category::class, [
            'id' => $childCategory_2->id,
            'parent_id' => $parentCategory->id,
        ]);
    }

    public function test_move_categories_to_root()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $childCategory_1 = Category::factory()->create([
            'name' => 'Child Category 1',
            'parent_id' => null
        ]);

        $childCategory_2 = Category::factory()->create([
            'name' => 'Child Category 2',
            'parent_id' => null
        ]);

        $response = $this->post('/api/v1/categories/move', [
            'categories' => [
                strval($childCategory_1->id),
                strval($childCategory_2->id),
            ],
            'target' => null
        ]);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);
        
        $this->assertDatabaseHas(Category::class, [
            'id' => $childCategory_1->id,
            'parent_id' => null,
        ]);
        
        $this->assertDatabaseHas(Category::class, [
            'id' => $childCategory_2->id,
            'parent_id' => null,
        ]);
    }

    public function test_delete_category()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $category = Category::factory()->create([
            'name' => 'Test Category'
        ]);

        $this->assertDatabaseHas(Category::class, [
            'name' => 'Test Category'
        ]);

        $response = $this->delete('/api/v1/categories/' . $category->id);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertSoftDeleted(Category::class, [
            'name' => 'Test Category'
        ]);

    }
}
