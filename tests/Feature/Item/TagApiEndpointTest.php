<?php

namespace Tests\Feature\Item;

use Tests\TestCase;

class TagApiEndpointTest extends TestCase
{
    public function test_list_tags()
    {
        $response = $this->get('api/v1/tags');

        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'title',
                    'data' => [
                        'tags' => [
                            '*' => [
                                'id',
                                'name',
                            ]
                        ]
                    ],
                ]);
    }
}