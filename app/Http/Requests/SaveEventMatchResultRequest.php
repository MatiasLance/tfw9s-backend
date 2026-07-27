<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEventMatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('is_abandoned_match')) {
            return;
        }

        $value = $this->input('is_abandoned_match');

        if (! is_string($value)) {
            return;
        }

        $normalizedValue = strtolower(trim($value));

        if (in_array($normalizedValue, ['1', 'true', 'on', 'yes'], true)) {
            $this->merge(['is_abandoned_match' => true]);
        } elseif (in_array($normalizedValue, ['0', 'false', 'off', 'no'], true)) {
            $this->merge(['is_abandoned_match' => false]);
        }
    }

    public function rules(): array
    {
        return [
            'team1_score' => ['required', 'integer', 'min:0', 'max:999'],
            'team2_score' => ['required', 'integer', 'min:0', 'max:999'],
            'is_abandoned_match' => ['sometimes', 'boolean'],
        ];
    }
}
