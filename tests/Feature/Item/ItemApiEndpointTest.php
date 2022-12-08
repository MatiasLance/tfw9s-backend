<?php

namespace Tests\Feature\Item;

use App\Models\Category;
use App\Models\Element;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemTags;
use App\Models\ItemVariantElement;
use App\Models\Media;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Item\Filter;
use App\Modules\Item\Variant;
use Database\Seeders\VariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
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
        $this->seedDatabase();

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

    public function test_filter_items()
    {
        $this->seedDatabase();

        $item = Item::factory()
                ->hasCategories()
                ->hasTags()
                ->hasElements()
                ->create([
                    'name' => 'V8 Engine'
                ]);

        $item->categories()->save(Category::find(2));
        $item->tags()->save(Tag::find(1));

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
        $this->seedDatabase();

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
                        ->hasElements(3)
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
                            'elements' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'price',
                                    'stock',
                                    'thumbnail',
                                ],
                            ],
                        ],
                        'variants' => [
                            '*' => [
                                'id',
                                'name',
                                'elements' => [
                                    '*' => [
                                        'id',
                                        'name',
                                        'thumbnail',
                                    ],
                                ],
                            ]
                        ],
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
        $this->seedDatabase();
        $elements = Element::pluck('id');
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
            'tags' => $tags,
            'elements' => $this->generateTestItemElementData($elements),
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
                            'elements' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'stock',
                                    'price',
                                ],
                            ],
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

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[1],
            'stock' => 2,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[2],
            'stock' => 0,
            'price' => null,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[3],
            'stock' => 1,
            'price' => 1400,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[4],
            'stock' => 5,
            'price' => null,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_COLOR,
            'thumbnail_color_value' => '#ffffff',
        ]);

        Storage::disk('public')->assertExists($media->path);
    }

    public function test_duplicate_item_as_is()
    {
        $this->seedDatabase();
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
                            ->hasElements(3)
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
                        ]
                    ],
                ]);

        $duplicate = Item::whereNot('id', $item->id)
                            ->where('name', $item->name)
                            ->where('description', $item->description)
                            ->where('price', $item->centPrice())
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

        $originalElements = $item->elements->sortBy('element_id')->pluck('element_id');
        $duplicateElements = $duplicate->elements->sortBy('element_id')->pluck('element_id');

        $this->assertEquals(json_encode($originalElements), json_encode($duplicateElements), 'Elements did not match with original item');
    }

    public function test_duplicate_item_with_overrides()
    {
        $this->seedDatabase();
        $elements = Element::pluck('id');
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
                            ->hasElements(3)
                            ->create();
        $category_1 = Category::factory()->create();
        $category_2 = Category::factory()->create();
        $tags = Tag::factory(3)->create()->pluck('id')->toArray();
        $thumbnail = UploadedFile::fake()->image('test_item_photo.jpg');

        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/items/duplicate/' . $item->id, [
            'name' => 'Cloned item',
            'tags' => $tags,
            'elements' => $this->generateTestItemElementData($elements),
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
                        ]
                    ],
                ]);

        $duplicate = Item::whereNot('id', $item->id)
                            ->where('name', 'Cloned item')
                            ->where('description', $item->description)
                            ->where('price', $item->centPrice())
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

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $duplicate->id,
            'element_id' => $elements[1],
            'stock' => 2,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $duplicate->id,
            'element_id' => $elements[2],
            'stock' => 0,
            'price' => null,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $duplicate->id,
            'element_id' => $elements[3],
            'stock' => 1,
            'price' => 1400,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $duplicate->id,
            'element_id' => $elements[4],
            'stock' => 5,
            'price' => null,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_COLOR,
            'thumbnail_color_value' => '#ffffff',
        ]);

        Storage::disk('public')->assertExists($media->path);
    }

    public function test_update_item()
    {
        $this->seedDatabase();
        $elements = Element::pluck('id');
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
                            ->hasElements(3)
                            ->create();
        $oldMedia = $item->media->first();
        $oldMediaFileName = explode('/', $oldMedia->path)[2];
        $oldCategories = $item->categories;
        UploadedFile::fake()
                        ->image('test')
                        ->storePubliclyAs('media/items', $oldMediaFileName, 'public');
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
            'tags' => $tags,
            'elements' => $this->generateTestItemElementData($elements),
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

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[1],
            'stock' => 2,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[2],
            'stock' => 0,
            'price' => null,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[3],
            'stock' => 1,
            'price' => 1400,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[4],
            'stock' => 5,
            'price' => null,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_COLOR,
            'thumbnail_color_value' => '#ffffff',
        ]);
    }

    public function test_update_item_without_thumbnail_changes()
    {
        $this->seedDatabase();
        $elements = Element::pluck('id');
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
                            ->hasElements(3)
                            ->create();
        $oldMedia = $item->media->first();
        $oldMediaFileName = explode('/', $oldMedia->path)[2];
        UploadedFile::fake()
                        ->image('test')
                        ->storePubliclyAs('media/items', $oldMediaFileName, 'public');
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
            'elements' => $this->generateTestItemElementData($elements),
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
        ]);

        $item->refresh();

        $media = $item
                    ->media
                    ->first();

        Storage::disk('public')->assertExists($oldMedia->path);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[1],
            'stock' => 2,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[2],
            'stock' => 0,
            'price' => null,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[3],
            'stock' => 1,
            'price' => 1400,
        ]);

        $this->assertDatabaseHas(ItemVariantElement::class, [
            'item_id' => $item->id,
            'element_id' => $elements[4],
            'stock' => 5,
            'price' => null,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_COLOR,
            'thumbnail_color_value' => '#ffffff',
        ]);
    }

    public function test_delete_item()
    {
        $this->seedDatabase();
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
                            ->hasElements()
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

        foreach ($item->elements as $element) {
            $this->assertDatabaseMissing(ItemVariantElement::class, [
                'id' => $element->id,
                'item_id' => $item->id,
            ]);
        }
    }

    /**
     * Seed the database with test data for Item Variant API endpoints
     */
    protected function seedDatabase()
    {
        $this->seed(VariantSeeder::class);

        Item::factory()
                ->count(10)
                ->hasCategories()
                ->hasTags()
                ->hasElements(3)
                ->create();
    }

    /**
     * Generate test data for item elements
     * 
     * @param Collection|array $elementIds Array containing IDs of existing elements
     * 
     * @return array
     */
    protected function generateTestItemElementData($elementIds): array
    {
        $elementThumbnail_1 = UploadedFile::fake()->image('test_element_thumbnail_1.jpg', 15, 20);
        $elementThumbnail_2 = UploadedFile::fake()->image('test_element_thumbnail_2.jpg', 15, 20);

        return [
            [
                'element_id' => $elementIds[1],
                'stock' => 2,
            ],
            [
                'element_id' => $elementIds[2],
                'stock' => 0,
                'price' => null,
            ],
            [
                'element_id' => $elementIds[3],
                'stock' => 1,
                'price' => 1400,
            ],
            [
                'element_id' => $elementIds[4],
                'stock' => 5,
                'price' => null,
                'thumbnail_type' => Variant::THUMBNAIL_TYPE_COLOR,
                'thumbnail' => '#ffffff',
            ],
            [
                'element_id' => $elementIds[5],
                'stock' => 8,
                'price' => null,
                'thumbnail_type' => Variant::THUMBNAIL_TYPE_IMAGE,
                'thumbnail' => $elementThumbnail_1,
            ],
            [
                'element_id' => $elementIds[6],
                'stock' => 9,
                'thumbnail_type' => Variant::THUMBNAIL_TYPE_IMAGE,
                'thumbnail' => $elementThumbnail_2,
            ],
        ];
    }
}
