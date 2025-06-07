<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBatchRequest extends FormRequest
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
            'batch_number' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'production_date' => 'nullable|date', 
            'expiration_date' => 'required|date',
            'purchase_entries_id' => 'nullable|integer',
        ];
    }
}
