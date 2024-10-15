<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingsRequest extends FormRequest
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
        'name' => ['required', 'string', 'max:255'],
        'address' => ['nullable', 'string', 'max:255'],
        'city' => ['nullable', 'string', 'max:255'],
        'state' => ['nullable', 'string', 'max:255'],
        'country' => ['nullable', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:255'],
        'email' => ['nullable', 'string', 'email', 'max:255'],
        'website' => ['nullable', 'string', 'max:255'],
        'facebook' => ['nullable', 'string', 'max:255'],
        'twitter' => ['nullable', 'string', 'max:255'],
        'instagram' => ['nullable', 'string', 'max:255'],
        'linkedin' => ['nullable', 'string', 'max:255'],
        'youtube' => ['nullable', 'string', 'max:255'],
        'logo_white' => ['nullable', 'string', 'max:255'],
        'logo_black' => ['nullable', 'string', 'max:255'],
        'rut' => ['nullable', 'string', 'max:255'],
        'allow_registration' => ['required', 'boolean'],
      ];
    }

}
