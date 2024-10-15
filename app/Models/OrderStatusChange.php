<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'change_type',
        'old_status',
        'new_status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
