<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPriceList extends Model
{
    protected $fillable = ['price_list_id', 'client_id'];
    
}
