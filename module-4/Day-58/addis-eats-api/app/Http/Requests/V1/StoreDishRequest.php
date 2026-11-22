<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $updates = [];

        if ($this->has('name') && is_string($this->name)) {
            $updates['name'] = trim($this->name);
        }

        if ($this->has('area') && is_string($this->area)) {
            $updates['area'] = trim($this->area);
        }

        if ($updates !== []) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        return [
            'name'          => ['bail', 'required', 'string', 'max:120'],
            'restaurant_id' => ['bail', 'required', 'integer', 'exists:restaurants,id'],
            'price'         => ['bail', 'required', 'integer', 'min:100', 'max:1000000'],
            'is_fasting'    => ['required', 'boolean'],
            'area'          => ['bail', 'required', 'string', Rule::in(config('addiseats.zones', []))],
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'The price must be a positive amount of at least 100 santim (1.00 ETB).',
            'area.in'   => 'Addis Eats does not deliver to that area yet.',
        ];
    }
}
