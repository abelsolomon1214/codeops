<?php

namespace App\Http\Requests\V1;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status') && is_string($this->status)) {
            $this->merge([
                'status' => strtolower(trim($this->status)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['bail', 'required', 'string', Rule::enum(OrderStatus::class)],
        ];
    }
}
