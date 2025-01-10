<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmailConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stores_email_config' => 'sometimes|boolean',
            'mail_host' => 'required_if:stores_email_config,1|string',
            'mail_port' => 'required_if:stores_email_config,1|string',
            'mail_username' => 'required_if:stores_email_config,1|string',
            'mail_password' => 'required_if:stores_email_config,1|string',
            'mail_encryption' => 'required_if:stores_email_config,1|string',
            'mail_from_address' => 'required_if:stores_email_config,1|email',
            'mail_from_name' => 'required_if:stores_email_config,1|string',
            'mail_reply_to_address' => 'nullable|email',
            'mail_reply_to_name' => 'nullable|string',
        ];
    }
}