<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $categories = [
            [
                'name' => 'Slides & Sandals',
                'children' => [],
            ],
            [
                'name' => 'Accessories',
                'children' => [],
            ],
            [
                'name' => 'Hoodies & Jumpers',
                'children' => []
            ],
            [
                'name' => 'Shirts',
                'children' => []
            ],
            [
                'name' => 'Sneakers',
                'children' => []
            ],
            [
                'name' => 'Brands',
                'children' => [
                    [
                        'name' => 'Nike',
                        'children' => [
                            [
                                'name' => 'Air Force 1',
                                'children' => [],
                            ],
                            [
                                'name' => 'Air Max 90',
                                'children' => [],
                            ],
                            [
                                'name' => 'Air Huarache',
                                'children' => [],
                            ],
                            [
                                'name' => 'Air Max TN',
                                'children' => [],
                            ],
                            [
                                'name' => 'Air Max 95',
                                'children' => [],
                            ],
                            [
                                'name' => 'Air Max 97',
                                'children' => [],
                            ],
                            [
                                'name' => 'Air Vapormax',
                                'children' => [],
                            ],
                            [
                                'name' => 'Dunk',
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Jordan',
                        'children' => [
                            [
                                'name' => 'Jordan 1',
                                'children' => [],
                            ],
                            [
                                'name' => 'Jordan 3',
                                'children' => [],
                            ],
                            [
                                'name' => 'Jordan 4',
                                'children' => [],
                            ],
                            [
                                'name' => 'Jordan 5',
                                'children' => [],
                            ],
                            [
                                'name' => 'Jordan 6',
                                'children' => [],
                            ],
                            [
                                'name' => 'Jordan 11',
                                'children' => [],
                            ],
                            [
                                'name' => 'Jordan 12',
                                'children' => [],
                            ]
                        ],
                    ],
                    [
                        'name' => 'Adidas',
                        'children' => [
                            [
                                'name' => 'Continental 80',
                                'children' => []
                            ],
                            [
                                'name' => 'Gazelle',
                                'children' => []
                            ],
                            [
                                'name' => 'NMD',
                                'children' => []
                            ],
                            [
                                'name' => 'Pharrell Human',
                                'children' => []
                            ],
                            [
                                'name' => 'Stan Smith',
                                'children' => []
                            ],
                            [
                                'name' => 'Superstar',
                                'children' => []
                            ],
                            [
                                'name' => 'Ultra-boost',
                                'children' => []
                            ],
                            [
                                'name' => 'Yeezy',
                                'children' => []
                            ],

                        ],
                    ],
                    [
                        'name' => 'New Balance',
                        'children' => [],
                    ],
                    [
                        'name' => 'Reebok',
                        'children' => [],
                    ],
                    [
                        'name' => 'Puma',
                        'children' => [],
                    ],
                    [
                        'name' => 'Asics',
                        'children' => [],
                    ],
                    [
                        'name' => 'Converse',
                        'children' => [
                            [
                                'name' => 'CDG Play',
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Basketball Shoes',
                        'children' => [],
                    ],
                    [
                        'name' => 'Giuseppe Zanotti',
                        'children' => [],
                    ],
                    [
                        'name' => 'Billionaire Boys',
                        'children' => []
                    ],
                    [
                        'name' => 'Ice Cream',
                        'children' => []
                    ],
                    [
                        'name' => 'Moschino',
                        'children' => []
                    ],
                    [
                        'name' => 'Salvatore',
                        'children' => []
                    ],
                    [
                        'name' => 'Alexander',
                        'children' => []
                    ],
                    [
                        'name' => 'Bally Mirror',
                        'children' => []
                    ]
                ],
            ],
        ];

        foreach ($categories as $category) {
            $this->generateCategory($category);
        }
    }

    protected function generateCategory($category, $parentId = null)
    {
        $x = new Category();
        $x->name = $category['name'];
        $x->parent_id = $parentId;
        $x->save();

        foreach ($category['children'] as $child) {
            $this->generateCategory($child, $x->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
