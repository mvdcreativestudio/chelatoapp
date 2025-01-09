<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadCompanyInformation extends Model
{
    use HasFactory;

    protected $table = 'lead_company_information';

    protected $fillable = [
        'lead_id',
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'webpage',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}
