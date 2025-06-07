<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar si usás policies
    }

    public function rules(): array
    {
        $filtered = collect($this->input('products', []))
            ->filter(fn ($item) => isset($item['quantity']) && $item['quantity'] > 0);

        $rules = [
            'from_store_id' => ['required', 'exists:stores,id'],
            'to_store_id' => ['required', 'exists:stores,id'],
            'products' => ['required', 'array'],
        ];

        foreach ($filtered as $index => $item) {
            $rules["products.{$index}.product_id"] = ['required', 'exists:products,id'];
            $rules["products.{$index}.quantity"] = ['required', 'numeric', 'min:1'];
        }

        return $rules;
    }


    public function messages(): array
    {
        return [
            'to_store_id.required' => 'Debe seleccionar una tienda destino.',
            'products.required' => 'Debe seleccionar al menos un producto.',
            'products.*.product_id.required' => 'Falta el producto.',
            'products.*.quantity.min' => 'La cantidad debe ser mayor que 0.',
        ];
    }
}
