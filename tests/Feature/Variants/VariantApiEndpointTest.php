<?php

namespace Tests\Feature\Variant;

use App\Models\Element;
use App\Models\Media;
use App\Models\User;
use App\Models\Variant as VariantModel;
use App\Modules\Item\Variant;
use Database\Seeders\VariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VariantApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_variants()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->seed(VariantSeeder::class);

        $response = $this->get('/api/v1/variants/');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
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
                            ]
                        ]
                    ],
                ],
            ]);
    }

    public function test_retrieve_variant()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->seed(VariantSeeder::class);
        
        $variantName = 'Test update';
        $variant = VariantModel::factory()->create([
            'name' => $variantName
        ]);

        $response = $this->get('/api/v1/variants/' . $variant->id);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.variant.name', $variantName)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'variant' => [
                        'id',
                        'name',
                        'elements' => [
                            '*' => [
                                'id',
                                'name',
                                'thumbnail',
                            ],
                        ]
                    ]
                ],
            ]);
    }

    public function test_retrieve_element()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->seed(VariantSeeder::class);

        $variant = VariantModel::factory()->create();
        $elementName = 'Test Element';
        $element = Element::factory()->create([
            'variant_id' => $variant,
            'name' => $elementName
        ]);

        $response = $this->get('/api/v1/variants/elements/' . $element->id);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.element.name', $elementName)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'element' => [
                        'id',
                        'name',
                        'thumbnail',
                    ],
                ],
            ]);
    }

    public function test_create_variant()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $variantName = 'New Test Variant';

        $response = $this->post('/api/v1/variants/', [
            'name' => $variantName
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'variant' => [
                        'id',
                        'name',
                    ],
                ],
            ]);
        $this->assertDatabaseHas(VariantModel::class, [
            'name' => $variantName
        ]);
    }

    public function test_create_element_no_thumbnail()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $parentVariant = VariantModel::factory()->create();

        $elementName = 'New Test Element';

        $response = $this->post('/api/v1/variants/' . $parentVariant->id, [
            'name' => $elementName,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'variant' => [
                        'id',
                        'name',
                        'elements' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas(Element::class, [
            'variant_id' => $parentVariant->id,
            'name' => $elementName,
            'thumbnail_type' => null,
        ]);
    }

    public function test_create_element_color_thumbnail()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $parentVariant = VariantModel::factory()->create();

        $elementName = 'New Test Element';

        $response = $this->post('/api/v1/variants/' . $parentVariant->id, [
            'name' => $elementName,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_COLOR,
            'thumbnail' => '#000',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'variant' => [
                        'id',
                        'name',
                        'elements' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas(Element::class, [
            'variant_id' => $parentVariant->id,
            'name' => $elementName,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_COLOR,
        ]);
    }

    public function test_create_element_image_thumbnail()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $parentVariant = VariantModel::factory()->create();
        
        $elementName = 'New Test Element';
        $elementThumbnail = UploadedFile::fake()->image('test_element_thumbnail_1.jpg', 15, 20);

        $response = $this->post('/api/v1/variants/' . $parentVariant->id, [
            'name' => $elementName,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_IMAGE,
            'thumbnail' => $elementThumbnail,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'variant' => [
                        'id',
                        'name',
                        'elements' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas(Element::class, [
            'variant_id' => $parentVariant->id,
            'name' => $elementName,
            'thumbnail_type' => Variant::THUMBNAIL_TYPE_IMAGE,
        ]);
    }

    public function test_update_variant()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $defaultVariantName = 'Default test variant name';
        $updatedVariantName = 'Updated test variant name';
        
        $newVariant = VariantModel::factory()
                                        ->create([
                                            'name' => $defaultVariantName
                                        ]);

        $response = $this->patch('/api/v1/variants/' . $newVariant->id, [
            'name' => $updatedVariantName
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);

        $this->assertDatabaseMissing(VariantModel::class, [
            'name' => $defaultVariantName
        ]);

        $this->assertDatabaseHas(VariantModel::class, [
            'name' => $updatedVariantName
        ]);
    }

    public function test_update_element()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $defaultElementName = 'Default test element name';
        $updatedElementName = 'Updated test element name';
        $defaultElementThumbnailType = Variant::THUMBNAIL_TYPE_COLOR;
        $defaultElementThumbnailColor = '#ffffff';
        
        $newVariant = VariantModel::factory()->create();
        $newElement = Element::factory()->create([
            'variant_id' => $newVariant->id,
            'name' => $defaultElementName
        ]);

        $response = $this->patch('/api/v1/variants/elements/' . $newElement->id, [
            'name' => $updatedElementName,
            'thumbnail_type' => $defaultElementThumbnailType,
            'thumbnail' => $defaultElementThumbnailColor,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);

        $this->assertDatabaseMissing(Element::class, [
            'name' => $defaultElementName
        ]);

        $this->assertDatabaseHas(Element::class, [
            'name' => $updatedElementName,
            'thumbnail_type' => $defaultElementThumbnailType,
            'thumbnail_color_value' => $defaultElementThumbnailColor,
        ]);
    }

    public function test_update_element_image_thumbnail()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $defaultElementName = 'Default test element name';
        $updatedElementName = 'Updated test element name';
        $defaultElementThumbnailType = Variant::THUMBNAIL_TYPE_IMAGE;
        $defaultElementThumbnailImage = UploadedFile::fake()->image('test_element_thumbnail_1.jpg', 15, 20);
        
        $newVariant = VariantModel::factory()->create();
        $newElement = Element::factory()->create([
            'variant_id' => $newVariant->id,
            'name' => $defaultElementName,
            'thumbnail_type' => null,
            'thumbnail_color_value' => null,
        ]);

        $response = $this->patch('/api/v1/variants/elements/' . $newElement->id, [
            'name' => $updatedElementName,
            'thumbnail_type' => $defaultElementThumbnailType,
            'thumbnail' => $defaultElementThumbnailImage,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);

        $this->assertDatabaseMissing(Element::class, [
            'name' => $defaultElementName
        ]);

        $this->assertDatabaseHas(Element::class, [
            'name' => $updatedElementName,
            'thumbnail_type' => $defaultElementThumbnailType,
            'thumbnail_color_value' => null,
        ]);

        $this->assertDatabaseHas(Media::class, [
            'mediable_id' => $newElement->id,
            'mediable_type' => $newElement::class,
        ]);
    }

    public function test_delete_variant()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $variant = VariantModel::factory()->create();

        $response = $this->delete('/api/v1/variants/' . $variant->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);

        $this->assertDatabaseMissing(VariantModel::class, [
            'id' => $variant->id
        ]);
    }

    public function test_delete_element()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );
        
        $variant = VariantModel::factory()->create();
        $element = Element::factory()->create([
            'variant_id' => $variant->id,
        ]);

        $response = $this->delete('/api/v1/variants/elements/' . $element->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);

        $this->assertDatabaseMissing(Element::class, [
            'id' => $element->id
        ]);
    }
}