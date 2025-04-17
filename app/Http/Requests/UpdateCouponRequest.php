<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'string|max:255|unique:coupons,code,' . $this->route('coupon'),
            'type' => 'string|in:fixed,percentage',
            'amount' => 'numeric|min:0',
            'init_date' => 'nullable|date|before_or_equal:due_date',
            'due_date' => 'nullable|date|after_or_equal:init_date',
            'excluded_products' => 'nullable|array',
            'excluded_products.*' => 'exists:products,id',
            'excluded_categories' => 'nullable|array',
            'excluded_categories.*' => 'exists:product_categories,id',
            'single_use' => 'nullable|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'excluded_products.*.exists' => 'Uno o más productos seleccionados no existen.',
            'excluded_categories.*.exists' => 'Una o más categorías seleccionadas no existen.',
            'due_date.after_or_equal' => 'La fecha de expiración no puede ser anterior a la fecha de inicio.',
            'init_date.before_or_equal' => 'La fecha de inicio no puede ser posterior a la fecha de expiración.',
        ];
    }
}
