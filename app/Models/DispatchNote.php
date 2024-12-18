<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchNote extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo
     */
    protected $table = 'dispatch_note';

    /**
     * Atributos 
     */
    protected $fillable = [
        'order_id',
        'date',
        'quantity',
        'bombing_type',
        'delivery_method',
        'product_id',
    ];


    /**
     * Relacion con el modelo Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relacion con el modelo Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relacion con el modelo NoteDelivery
     */
    public function noteDelivery()
    {
        return $this->hasMany(NoteDelivery::class);
    }
}
