<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'user_creator_id', 'client_id','archived','store_id','name','description','amount_of_money','category_id','phone','email','position'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_creator_id');
    }

    public function tasks()
    {
        return $this->hasMany(LeadTask::class, 'leads_id', 'id');
    }

    public function companyInformation()
    {
        return $this->hasOne(LeadCompanyInformation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }
}
