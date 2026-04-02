<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_type',
        'user_id',
        'type',
        'quantity',
        'old_stock',
        'new_stock',
        'reason',
    ];

    public function product(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registra un movimiento de stock.
     */
    public static function record(
        Model $product,
        string $type,
        int $quantity,
        int $oldStock,
        int $newStock,
        ?string $reason = null
    ): self {
        return self::create([
            'product_id' => $product->id,
            'product_type' => get_class($product),
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => $quantity,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'reason' => $reason,
        ]);
    }

    /**
     * Devuelve la etiqueta legible del tipo de movimiento.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'manual' => 'Ajuste manual',
            'sale' => 'Venta',
            'order_delete' => 'Eliminación de orden',
            'credit_note' => 'Nota de crédito',
            default => $this->type,
        };
    }
}
