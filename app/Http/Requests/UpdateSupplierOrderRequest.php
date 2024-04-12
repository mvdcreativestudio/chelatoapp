<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierOrderRequest extends FormRequest
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
              'notes' => 'nullable|string',
              'raw_material_id.*' => 'sometimes|distinct|exists:raw_materials,id',
              'quantity.*' => 'sometimes|numeric|min:1'
          ];
      }
}
