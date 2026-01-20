<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !$this->user()->isAdmin()) {
                        $fail('You are not authorized to create orders for other users.');
                    }
                },
            ],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ];
    }

    // custom validation error messages
    public function messages(): array
    {
        return [
            'items.required' => 'An order must contain at least one item.',
            'items.min' => 'An order must contain at least one item.',
            'items.*.product_name.required' => 'Each item must have a product name.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 1000.',
            'items.*.unit_price.required' => 'Each item must have a unit price.',
            'items.*.unit_price.min' => 'Unit price must be greater than 0.',
        ];
    }


    // custom attributes for validation errors
    public function attributes(): array
    {
        return [
            'items.*.product_name' => 'product name',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('user_id') && $this->user()->isCustomer()) {
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        }
    }
}
