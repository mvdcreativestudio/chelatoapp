<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'budget_id' => 'required|exists:budgets,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,pending_approval,sent,negotiation,approved,rejected,expired,cancelled',
        ];
    }
}