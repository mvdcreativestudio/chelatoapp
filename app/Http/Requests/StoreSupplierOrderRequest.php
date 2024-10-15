<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    */
    public function rules(): array
    {
      return [
          'supplier_id' => 'required|exists:suppliers,id',
          'order_date' => 'required|date',
          'shipping_status' => 'required|string',
          'payment_status' => 'required|string',
          'payment' => 'required|numeric|min:0',
          'payment_method' => 'required|string',
          'notes' => 'nullable|string',
          'raw_material_id.*' => 'required|distinct|exists:raw_materials,id',
          'quantity.*' => 'required|numeric|min:1'
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
            'raw_material_id.*.required' => 'La materia prima es obligatoria.',
            'raw_material_id.*.distinct' => 'Las materias primas duplicadas no están permitidas.',
            'raw_material_id.*.exists' => 'La materia prima seleccionada no es válida.',
            'quantity.*.required' => 'La cantidad es obligatoria.',
            'payment.numeric' => 'El pago debe ser un número.',
            'payment.min' => 'El pago no puede ser negativo.',
            'quantity.*.numeric' => 'La cantidad debe ser un número.',
            'quantity.*.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
