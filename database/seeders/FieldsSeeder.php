<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Region;
use Illuminate\Database\Seeder;
use RuntimeException;

class FieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fieldNames = [
            'Bayside Community Oval',
            'Centennial Park Sports Ground',
            'Harbour View Football Field',
            'Hills Regional Sports Complex',
            'Lakeside Championship Oval',
            'North Shore Athletics Field',
            'Riverside Recreation Ground',
            'Seaside Memorial Oval',
            'Western District Football Park',
            'Willow Creek Sports Reserve',
        ];

        $regions = Region::query()->orderBy('id')->take(count($fieldNames))->get();

        if ($regions->count() < count($fieldNames)) {
            throw new RuntimeException('FieldsSeeder requires at least 10 regions.');
        }

        foreach ($fieldNames as $index => $name) {
            $attributes = Field::factory()
                ->make([
                    'name' => $name,
                    'region_id' => $regions[$index]->id,
                ])
                ->getAttributes();

            $field = Field::withTrashed()->firstOrNew(['name' => $name]);
            $field->forceFill($attributes);

            if ($field->trashed()) {
                $field->restore();
            }

            $field->save();
        }
    }
}
