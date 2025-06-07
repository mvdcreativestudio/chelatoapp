<?php

namespace App\Models;

use App\Enums\CurrentAccounts\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrentAccountSettings extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_type',
        'late_fee',
        'payment_terms',
    ];

    protected $casts = [
        'transaction_type' => TransactionTypeEnum::class,
    ];

    /**
     * Obtiene los creditos iniciales asociados a esta configuración de cuenta corriente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function initialCredits()
    {
        return $this->hasMany(CurrentAccountInitialCredit::class);
    }
}
