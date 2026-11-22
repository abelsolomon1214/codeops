<?php

namespace App\Http\Requests\V1;

use App\Models\Dish;
use App\Rules\TeleBirrNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $updates = [];

        if ($this->has('area') && is_string($this->area)) {
            $updates['area'] = trim($this->area);
        }

        if ($this->has('phone') && is_string($this->phone)) {
            $phone = preg_replace('/[\s\-]/', '', $this->phone);
            $phone = preg_replace('/^(\+251|251)/', '0', $phone);
            $updates['phone'] = $phone;
        }

        if ($this->has('notes') && is_string($this->notes)) {
            $updates['notes'] = trim($this->notes);
        }

        if ($updates !== []) {
            $this->merge($updates);
        }
    }

    public function rules(): array
    {
        return [
            'area'            => ['bail', 'required', 'string', Rule::in(config('addiseats.zones', []))],
            'phone'           => ['bail', 'required', 'string', new TeleBirrNumber()],
            'notes'           => ['nullable', 'string', 'max:500'],
            'lines'           => ['bail', 'required', 'array', 'min:1', 'max:30'],
            'lines.*.dish_id' => [
                'bail',
                'required',
                'integer',
                'distinct',
                Rule::exists('dishes', 'id')->whereNull('deleted_at'),
            ],
            'lines.*.qty'     => ['bail', 'required', 'integer', 'between:1,50'],
        ];
    }

    public function messages(): array
    {
        return [
            'area.in'                  => 'Addis Eats does not deliver to that area yet.',
            'lines.*.dish_id.distinct' => 'Duplicate dishes are not allowed in the same order; combine quantities instead.',
            'lines.*.dish_id.exists'   => 'The selected dish is unavailable or does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'lines.*.dish_id' => 'dish',
            'lines.*.qty'     => 'quantity',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $ids    = collect($this->input('lines', []))->pluck('dish_id')->all();
                $dishes = Dish::whereIn('id', $ids)->get(['id', 'area', 'restaurant_id']);

                if ($dishes->pluck('area')->unique()->count() > 1) {
                    $validator->errors()->add('lines', 'All dishes in one order must be from the same area.');
                    return;
                }

                if ($dishes->isNotEmpty() && $dishes->first()->area !== $this->input('area')) {
                    $validator->errors()->add('area', 'The delivery area must match the area of the ordered dishes.');
                }

                if ($dishes->pluck('restaurant_id')->unique()->count() > 1) {
                    $validator->errors()->add('lines', 'All dishes in one order must belong to the same restaurant.');
                }
            },
        ];
    }
}
