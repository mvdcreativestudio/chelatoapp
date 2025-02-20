<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'init_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:init_date',
            'excluded_products' => 'nullable|array',
            'excluded_products.*' => 'exists:products,id', // Cada elemento debe existir en la tabla products
            'excluded_categories' => 'nullable|array',
            'excluded_categories.*' => 'exists:product_categories,id', // Cada elemento debe existir en la tabla product_categories
        ];
    }

    public function messages()
    {
        return [
            'excluded_products.*.exists' => 'Uno o más productos seleccionados no existen.',
            'excluded_categories.*.exists' => 'Una o más categorías seleccionadas no existen.',
            'due_date.after_or_equal' => 'La fecha de expiración no puede ser anterior a la fecha de inicio.',
        ];
    }
}
