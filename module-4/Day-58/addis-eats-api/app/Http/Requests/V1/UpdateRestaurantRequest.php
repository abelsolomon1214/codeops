<?php

namespace App\Http\Requests\V1;

use App\Rules\TeleBirrNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRestaurantRequest extends FormRequest
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

        if ($this->has('slug') && is_string($this->slug)) {
            $updates['slug'] = trim($this->slug);
        }

        if ($this->has('area') && is_string($this->area)) {
            $updates['area'] = trim($this->area);
        }

        if ($this->has('phone') && is_string($this->phone)) {
            $phone = preg_replace('/[\s\-]/', '', $this->phone);
            $phone = preg_replace('/^(\+251|251)/', '0', $phone);
            $updates['phone'] = $phone;
        }

        if ($updates !== []) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'bail', 'required', 'string', 'max:120'],
            'slug'          => [
                'sometimes',
                'bail',
                'required',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('restaurants', 'slug')->ignore($this->route('restaurant')),
            ],
            'area'          => ['sometimes', 'bail', 'required', 'string', Rule::in(config('addiseats.zones', []))],
            'phone'         => ['sometimes', 'bail', 'required', 'string', new TeleBirrNumber()],
            'opening_hours' => ['sometimes', 'bail', 'required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'area.in' => 'Addis Eats does not deliver to that area yet.',
        ];
    }
}
