<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadAttachedFileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'file' => 'sometimes|string|max:255',
        ];
    }
}
