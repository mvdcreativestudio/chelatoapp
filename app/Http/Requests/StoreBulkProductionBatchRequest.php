<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkProductionBatchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bulk_production_id' => 'required|exists:bulk_productions,id',
            'batch_id' => 'required|exists:batches,id',
            'quantity_used' => 'required|numeric|min:0',
        ];
    }
}
