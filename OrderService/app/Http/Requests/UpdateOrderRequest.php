<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
        $orderId = $this->route('pedido') ? $this->route('pedido')->id : null;

        return [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'order_number' => "sometimes|required|string|unique:orders,order_number,{$orderId}|max:50",
            'status' => 'sometimes|required|in:pending,processing,completed,cancelled',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'order_date' => 'sometimes|required|date',
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
            'order_number.required' => 'El número de pedido es obligatorio',
            'order_number.unique' => 'Este número de pedido ya existe',
            'total_amount.required' => 'El monto total es obligatorio',
            'total_amount.numeric' => 'El monto total debe ser un número',
            'total_amount.min' => 'El monto total debe ser mayor o igual a 0',
            'order_date.required' => 'La fecha del pedido es obligatoria',
            'order_date.date' => 'La fecha del pedido debe ser una fecha válida',
            'status.in' => 'El estado debe ser: pending, processing, completed o cancelled',
        ];
    }
}
