<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadAttachedFileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'lead_id' => 'required|exists:leads,id',
            'file' => 'required|string|max:255',
        ];
    }
}
