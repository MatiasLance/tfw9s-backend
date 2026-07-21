<?php

namespace Tests\Feature\Item;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemTags;
use App\Models\Media;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Item\Filter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ItemApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown():void
    {
        Storage::disk('public')->deleteDirectory('/media/items');
        parent::tearDown();
    }

    /**
     * Test getting a list of items
     *
     * @return void
     */
    public function test_list_items()
    {
        $response = $this->get('/api/v1/items/');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'status',
                    'title',
                    'data' => [
                        'current_page',
                        'last_page',
                        'items_per_page',
                        'page_length',
                        'total_items',
                        'from',
                        'to',
                        'items' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                                'price',
                                'stock',
                                'categories' => [
                                    '*' => [
                                        'name'
                                    ]
                                ],
                                'tags' => [
                                    '*' => [
                                        'name'
                                    ]
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }

    public function test_hidden_items_are_excluded_before_public_pagination(): void
    {
        Item::factory()->count(17)->create(['is_active' => true]);
        Item::factory()->count(3)->create(['is_active' => false]);

        $firstPage = $this->getJson('/api/v1/items?maxItemsPerPage=15&page=1');

        $firstPage
            ->assertOk()
            ->assertJsonPath('data.total_items', 17)
            ->assertJsonPath('data.last_page', 2)
            ->assertJsonCount(15, 'data.items');

        $this->assertTrue(collect($firstPage->json('data.items'))->every('is_active'));

        $this->getJson('/api/v1/items?maxItemsPerPage=15&page=2')
            ->assertOk()
            ->assertJsonPath('data.total_items', 17)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_public_user_cannot_request_hidden_items(): void
    {
        Item::factory()->create(['is_active' => false]);

        $this->getJson('/api/v1/items?include_inactive=true')->assertForbidden();
    }

    public function test_admin_can_request_hidden_items(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);

        Item::factory()->create(['is_active' => true]);
        Item::factory()->create(['is_active' => false]);

        $this->getJson('/api/v1/items?include_inactive=true')
            ->assertOk()
            ->assertJsonPath('data.total_items', 2)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_hidden_item_cannot_be_retrieved_publicly(): void
    {
        $item = Item::factory()->create(['is_active' => false]);

        $this->getJson('/api/v1/items/' . $item->id)->assertNotFound();
    }

    public function test_filter_items()
    {
        $filter = [
            'q' => 'engine',
            'category' => 2,
            'tags' => 1,
            'sort' => Filter::SORT_LATEST,
            'page' => 1,
        ];
        $query = http_build_query($filter);

        $response = $this->get('/api/v1/items?' . $query);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'status',
                    'title',
                    'data' => [
                        'current_page',
                        'last_page',
                        'items_per_page',
                        'page_length',
                        'total_items',
                        'from',
                        'to',
                        'items' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                                'price',
                                'stock',
                                'categories' => [
                                    '*' => [
                                        'name'
                                    ]
                                ],
                                'tags' => [
                                    '*' => [
                                        'name'
                                    ]
                                ],
                                'media' => [
                                    '*' => [
                                        'path'
                                    ]
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }

    /**
     * Test retrieving a single item
     * 
     * @return void
     */
    public function test_retrieve_item()
    {
        $itemName = 'V8 Engine';
        $item = Item::factory()
                        ->has(
                            Tag::factory()
                                ->count(3)
                        )
                        ->has(
                            Category::factory()
                        )
                        ->has(
                            Media::factory()
                        )
                        ->create(
                            [
                                'name' => $itemName,
                                'price' => 12.50,
                            ]
                        );

        $response = $this->get('/api/v1/items/' . $item->id);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.item.name', $itemName)
            ->assertJsonStructure(
                [
                    'status',
                    'title',
                    'data' => [
                        'item' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock',
                            'categoryLineages' => [
                                '*' => []
                            ],
                            'categories' => [
                                '*' => [
                                    'name'
                                ]
                            ],
                            'tags' => [
                                '*' => [
                                    'name'
                                ]
                            ],
                            'media' => [
                                '*' => [
                                    'hash',
                                    'path'
                                ]
                            ],
                        ]
                    ],
                ]
            )
            ->assertJsonFragment(
                [
                    'price' => 12.5
                ]
            );
    }

    public function test_create_item()
    {
        $user = User::factory()->create();
        $category_1 = Category::factory()->create();
        $category_2 = Category::factory()->create();
        $tags = Tag::factory(3)->create()->pluck('id')->toArray();
        $thumbnail_1 = UploadedFile::fake()->image('test_item_photo_1.jpg', 15, 20);
        $thumbnail_2 = UploadedFile::fake()->image('test_item_photo_2.jpg', 12, 50);

        Sanctum::actingAs($user);

        $response = $this->post('api/v1/items', [
            'name' => 'Test Item',
            'description' => 'This is a test item',
            'price' => 12.50,
            'stock' => 44,
            'tags' => $tags,
            'categoryId' => [
                strval($category_1->id), // Mimic HTTP Request Form Data
                strval($category_2->id), // Mimic HTTP Request Form Data
            ],
            'photo' => [
                $thumbnail_1,
                $thumbnail_2
            ],
        ]);

        $response
                ->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'data' => [
                        'item' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock',
                        ]
                    ],
                ]);

        $this->assertDatabaseHas(Item::class, [
            'name' => 'Test Item',
            'description' => 'This is a test item',
            'price' => 1250,
        ]);

        $item = Item::query()
                        ->where('name', 'Test Item')
                        ->where('description', 'This is a test item')
                        ->first();

        $media = $item
                    ->media
                    ->first();

        $this->assertDatabaseHas(ItemCategory::class, [
            'item_id' => $item->id,
            'category_id' => $category_1->id,
        ]);

        $this->assertDatabaseHas(ItemCategory::class, [
            'item_id' => $item->id,
            'category_id' => $category_2->id,
        ]);

        Storage::disk('public')->assertExists($media->path);
    }

    public function test_duplicate_item_as_is()
    {
        $user = User::factory()->create();
        $item = Item::factory()
                            ->has(
                                Category::factory()
                            )
                            ->has(
                                Tag::factory()->count(3)
                            )
                            ->has(
                                Media::factory()
                            )
                            ->create();

        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/items/duplicate/' . $item->id);

        $response
                ->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'data' => [
                        'item' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock',
                        ]
                    ],
                ]);

        $duplicate = Item::whereNot('id', $item->id)
                            ->where('name', $item->name)
                            ->where('description', $item->description)
                            ->where('price', $item->centPrice())
                            ->where('stock', $item->stock)
                            ->first();

        $this->assertInstanceOf(Item::class, $duplicate);

        $originalTags = $item->tags->sortBy('id')->pluck('id');
        $duplicateTags = $duplicate->tags->sortBy('id')->pluck('id');
        
        $this->assertEquals(json_encode($originalTags), json_encode($duplicateTags), 'Tags did not match with original item');

        $originalCategories = $item->categories->sortBy('id')->pluck('id');
        $duplicateCategories = $duplicate->categories->sortBy('id')->pluck('id');
        
        $this->assertEquals(json_encode($originalCategories), json_encode($duplicateCategories), 'Categories did not match with original item');

        $originalMedia = $item->media->sortBy('path')->pluck('path');
        $duplicateMedia = $duplicate->media->sortBy('path')->pluck('path');
        
        $this->assertEquals(json_encode($originalMedia), json_encode($duplicateMedia), 'Media did not match with original item');
    }

    public function test_duplicate_item_with_overrides()
    {
        $user = User::factory()->create();
        $item = Item::factory()
                            ->has(
                                Category::factory()
                            )
                            ->has(
                                Tag::factory()->count(3)
                            )
                            ->has(
                                Media::factory()
                            )
                            ->create();
        $category_1 = Category::factory()->create();
        $category_2 = Category::factory()->create();
        $tags = Tag::factory(3)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('test_item_photo.jpg');

        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/items/duplicate/' . $item->id, [
            'name' => 'Cloned item',
            'tags' => $tags,
            'categoryId' => [
                strval($category_1->id), // Mimic HTTP Request Form Data
                strval($category_2->id), // Mimic HTTP Request Form Data
            ],
            'photo' => [
                $thumbnail
            ],
        ]);

        $response
                ->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'data' => [
                        'item' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock',
                        ]
                    ],
                ]);

        $duplicate = Item::whereNot('id', $item->id)
                            ->where('name', 'Cloned item')
                            ->where('description', $item->description)
                            ->where('price', $item->centPrice())
                            ->where('stock', $item->stock)
                            ->first();

        $media = $duplicate
                    ->media
                    ->first();

        $this->assertInstanceOf(Item::class, $duplicate);

        $this->assertDatabaseHas(ItemCategory::class, [
            'item_id' => $duplicate->id,
            'category_id' => $category_1->id,
        ]);

        $this->assertDatabaseHas(ItemCategory::class, [
            'item_id' => $duplicate->id,
            'category_id' => $category_2->id,
        ]);

        Storage::disk('public')->assertExists($media->path);

    }

    public function test_update_item_x()
    {
        $user = User::factory()->create();
        $item = Item::factory()
                            ->has(
                                Category::factory()
                            )
                            ->has(
                                Tag::factory()->count(3)
                            )
                            ->has(
                                Media::factory()
                            )
                            ->create();
        $oldMedia = $item->media->first();
        $oldMediaFileName = explode('/', $oldMedia->path)[2];
        $oldCategories = $item->categories;
        UploadedFile::fake()
                        ->image('test')
                        ->storePubliclyAs(dirname($oldMedia->path), $oldMediaFileName, 'public');
        $category_new_1 = Category::factory()->create();
        $category_new_2 = Category::factory()->create();
        $tags = Tag::factory(3)
                        ->create()
                        ->pluck('id')
                        ->toArray();
        $thumbnail_1 = UploadedFile::fake()->image('test_item_photo_1.jpg', 15, 20);
        $thumbnail_2 = UploadedFile::fake()->image('test_item_photo_2.jpg', 12, 50);

        Sanctum::actingAs($user);

        $response = $this->patch('api/v1/items/' . $item->id, [
            'name' => 'Test Updated Item',
            'description' => 'Updated Item description',
            'price' => 32.30,
            'stock' => 53,
            'tags' => $tags,
            'categoryId' => [
                $category_new_1->id,
                $category_new_2->id,
            ],
            'photo' => [
                $thumbnail_1,
                $thumbnail_2,
                $oldMedia->hash
            ],
        ]);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertDatabaseHas(Item::class, [
            'name' => 'Test Updated Item',
            'description' => 'Updated Item description',
            'price' => 3230,
            'stock' => 53,
        ]);

        foreach ($oldCategories as $oldCategory) {
            $this->assertDatabaseMissing(ItemCategory::class, [
                'item_id' => $item->id,
                'category_id' => $oldCategory->id,
            ]);
        }

        $this->assertDatabaseHas(ItemCategory::class, [
            'item_id' => $item->id,
            'category_id' => $category_new_1->id,
        ]);

        $this->assertDatabaseHas(ItemCategory::class, [
            'item_id' => $item->id,
            'category_id' => $category_new_2->id,
        ]);

        $item->refresh();

        $this->assertEquals(3, $item->media->count(), 'Expected number of media for item did not match');

        Storage::disk('public')->assertExists($oldMedia->path);

        $thumbnail_1_hash = hash_file('sha256', $thumbnail_1);
        $thumbnail_1_exists = $item->media()->where('hash', $thumbnail_1_hash)->exists();
        $this->assertTrue($thumbnail_1_exists);

        $thumbnail_2_hash = hash_file('sha256', $thumbnail_2);
        $thumbnail_2_exists = $item->media()->where('hash', $thumbnail_2_hash)->exists();
        $this->assertTrue($thumbnail_2_exists);
    }

    public function test_update_item_without_thumbnail_changes()
    {
        $user = User::factory()->create();
        $item = Item::factory()
                            ->has(
                                Category::factory()
                            )
                            ->has(
                                Tag::factory()->count(3)
                            )
                            ->has(
                                Media::factory()
                            )
                            ->create();
        $oldMedia = $item->media->first();
        $oldMediaFileName = explode('/', $oldMedia->path)[2];
        UploadedFile::fake()
                        ->image('test')
                        ->storePubliclyAs(dirname($oldMedia->path), $oldMediaFileName, 'public');
        $category = Category::factory()->create();
        $tags = Tag::factory(3)
                        ->create()
                        ->pluck('id')
                        ->toArray();

        Sanctum::actingAs($user);

        $response = $this->patch('api/v1/items/' . $item->id, [
            'name' => 'Test Updated Item',
            'description' => 'Updated Item description',
            'price' => 32.30,
            'stock' => 53,
            'tags' => $tags,
            'categoryId' => [
                $category->id
            ],
            'photo' => null,
        ]);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertDatabaseHas(Item::class, [
            'name' => 'Test Updated Item',
            'description' => 'Updated Item description',
            'price' => 3230,
            'stock' => 53,
        ]);

        $item->refresh();

        $media = $item
                    ->media
                    ->first();

        Storage::disk('public')->assertExists($oldMedia->path);
    }

    public function test_delete_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()
                            ->has(
                                Category::factory()
                            )
                            ->has(
                                Tag::factory()->count(3)
                            )
                            ->has(
                                Media::factory()
                            )
                            ->create();

        Sanctum::actingAs($user);

        $response = $this->delete('api/v1/items/' . $item->id);

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                ]);

        $this->assertSoftDeleted($item);

        foreach ($item->categories as $category) {
            $this->assertDatabaseMissing(ItemCategory::class, [
                'item_id' => $item->id,
                'category_id' => $category->id,
            ]);
        }

        foreach ($item->tags as $tag) {
            $this->assertDatabaseMissing(ItemTags::class, [
                'item_id' => $item->id,
                'tag_id' => $tag->id,
            ]);
        }
    }
}
