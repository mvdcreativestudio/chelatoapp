<?php

namespace App\Http\Requests;

use App\Enums\Expense\ExpenseStatusEnum;
use App\Rules\AmountPaidNotGreaterThanExpense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateExpensePaymentMethodRequest extends FormRequest
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
        // dd($this->expense_id, $this->expense_payment_method);
        return [
            'amount_paid' => [
                'required',
                'numeric',
                new AmountPaidNotGreaterThanExpense($this->expense_id, $this->expense_payment_method),
            ],
            'payment_date' => 'required|date',
            'payment_method_id' => 'required|string',
            'expense_id' => 'required|integer|exists:expenses,id',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount_paid.required' => 'El campo monto pagado es requerido.',
            'amount_paid.numeric' => 'El campo monto pagado debe ser un número.',
            'payment_date.required' => 'El campo fecha de pago es requerido.',
            'payment_date.date' => 'El campo fecha de pago debe ser una fecha válida.',
            'payment_method_id.required' => 'El campo método de pago es requerido.',
            'payment_method_id.string' => 'El campo método de pago debe ser una cadena de texto.',
            'expense_id.required' => 'El campo ID del gasto es requerido.',
            'expense_id.integer' => 'El campo ID del gasto debe ser un número entero.',
            'expense_id.exists' => 'El gasto seleccionado no es válido.',
        ];
    }
}
