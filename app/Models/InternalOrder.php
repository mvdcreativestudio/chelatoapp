<?php

namespace App\Models;
use App\Enums\InternalOrders\InternalOrderStatus;
use Illuminate\Database\Eloquent\Model;

class InternalOrder extends Model
{
    protected $casts = [
      'status' => InternalOrderStatus::class,
      'delivery_date' => 'date',
    ];

    protected $fillable = [
        'from_store_id',
        'to_store_id',
        'status',
        'created_by',
        'delivery_date',
    ];

    // 🏬 Tienda que envía
    public function fromStore()
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    // 🏬 Tienda que recibe
    public function toStore()
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    // 📦 Productos solicitados en la orden
    public function items()
    {
        return $this->hasMany(InternalOrderItem::class);
    }

    // 💸 Factura relacionada (si la generamos luego)
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'accepted' => 'success',
            'cancelled' => 'danger',
            'delivered' => 'info',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            \App\Enums\InternalOrders\InternalOrderStatus::PENDING => 'Pendiente',
            \App\Enums\InternalOrders\InternalOrderStatus::ACCEPTED => 'Aceptada',
            \App\Enums\InternalOrders\InternalOrderStatus::cancelled => 'Rechazada',
            \App\Enums\InternalOrders\InternalOrderStatus::DELIVERED => 'Entregada',
            default => ucfirst($this->status->value),
        };
    }


    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function setStatus(string $status): void
    {
        if (InternalOrderStatus::tryFrom($status)) {
            $this->status = InternalOrderStatus::from($status);
        } else {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
    }
}
