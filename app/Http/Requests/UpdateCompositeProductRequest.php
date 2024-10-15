<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompositeProductRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Cambia esto según la lógica de autorización si es necesario.
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'], // Validación para el precio
            'recommended_price' => ['required', 'numeric'], // Validación para el precio recomendado
            'store_id' => ['required', 'exists:stores,id'],
            'products' => ['required', 'array'], // Asegura que 'products' sea un array
            'products.*.product_id' => ['required', 'exists:products,id'], // Asegura que cada 'product_id' existe
            'products.*.quantity' => ['required', 'integer', 'min:1'], // Asegura que cada 'quantity' sea un entero positivo
        ];
    }

    /**
     * Mensajes de validación personalizados.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'recommended_price.required' => 'El precio recomendado es obligatorio.',
            'recommended_price.numeric' => 'El precio recomendado debe ser un número.',
            'store_id.required' => 'La tienda es obligatoria.',
            'products.required' => 'Debes agregar al menos un producto.',
            'products.*.product_id.required' => 'El ID del producto es obligatorio.',
            'products.*.product_id.exists' => 'El producto seleccionado no existe.',
            'products.*.quantity.required' => 'La cantidad es obligatoria para cada producto.',
            'products.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'products.*.quantity.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
