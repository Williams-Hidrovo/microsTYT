<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'order_number' => 'nullable|string|unique:orders,order_number|max:50',
            'status' => 'nullable|in:pending,processing,completed,cancelled',
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'order_date' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'El cliente es obligatorio',
            'customer_id.exists' => 'El cliente seleccionado no existe',
            'order_number.unique' => 'Este número de pedido ya existe',
            'total_amount.required' => 'El monto total es obligatorio',
            'total_amount.numeric' => 'El monto total debe ser un número',
            'total_amount.min' => 'El monto total debe ser mayor o igual a 0',
            'order_date.required' => 'La fecha del pedido es obligatoria',
            'order_date.date' => 'La fecha del pedido debe ser una fecha válida',
            'status.in' => 'El estado debe ser: pending, processing, completed o cancelled',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('status')) {
            $this->merge([
                'status' => Order::STATUS_PENDING
            ]);
        }

        if (!$this->has('currency')) {
            $this->merge([
                'currency' => 'USD'
            ]);
        }
    }
}
