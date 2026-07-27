<?php

namespace App\Http\Requests;

use App\Modules\TeamPosition\Filter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTeamPositionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => [
                'nullable',
                Rule::in([
                    Filter::SORT_LATEST,
                    Filter::SORT_A_TO_Z,
                    Filter::SORT_Z_TO_A,
                    Filter::SORT_POINTS,
                ]),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
            'maxTeamPositionsPerPage' => ['nullable', 'integer', 'min:1', 'max:100'],
            'event' => ['nullable', 'integer', 'min:1'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'agegroup' => ['nullable', 'integer', 'min:1'],
            'series' => ['nullable', 'integer', 'min:1'],
            'region' => ['nullable', 'integer', 'min:1'],
            'round' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'q' => $validated['q'] ?? null,
            'sort' => $validated['sort'] ?? null,
            'page' => $validated['page'] ?? null,
            'max_teamPosition_per_page' => $validated['maxTeamPositionsPerPage'] ?? null,
            'event' => $validated['event'] ?? null,
            'year' => $validated['year'] ?? null,
            'agegroup' => $validated['agegroup'] ?? null,
            'series' => $validated['series'] ?? null,
            'region' => $validated['region'] ?? null,
            'round' => $validated['round'] ?? null,
        ];
    }
}
