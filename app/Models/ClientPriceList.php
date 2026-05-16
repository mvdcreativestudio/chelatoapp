<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPriceList extends Model
{
    use HasFactory;

    protected $table = 'client_price_lists';

    protected $fillable = [
        'client_id',
        'price_list_id',
    ];
}
