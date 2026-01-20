<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        return $this->user()->can('update', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        if ($this->has('status')) {
            $rules['status'] = [
                'required',
                'string',
                Rule::in(OrderStatus::values()),
            ];

            // manager can update status only. maybe? not cleared, so I am adding this restriction. /
            if (!$this->user()->isAdmin()) {
                $allowedFields = ['status'];
                $submittedFields = array_keys($this->all());
                $extraFields = array_diff($submittedFields, $allowedFields);

                if (!empty($extraFields)) {
                    $rules['_manager_restriction'] = [
                        function ($attribute, $value, $fail) use ($extraFields) {
                            $fail('You can only update the order status. Cannot modify: ' . implode(', ', $extraFields));
                        }
                    ];
                }
            }
        }

        // admin can update anything.
        if ($this->user()->isAdmin() && $this->has('items')) {
            $rules['items'] = ['array', 'min:1'];
            $rules['items.*.product_name'] = ['required', 'string', 'max:255'];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1', 'max:1000'];
            $rules['items.*.unit_price'] = ['required', 'numeric', 'min:0.01', 'max:999999.99'];
        }

        return $rules;
    }


    // custom error message
    public function messages(): array
    {
        return [
            'status.required' => 'Order status is required.',
            'status.in' => 'Invalid order status. Allowed values: ' . implode(', ', OrderStatus::values()),
        ];
    }
}
