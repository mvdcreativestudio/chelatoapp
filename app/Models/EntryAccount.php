<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo EntryAccount: representa una cuenta contable.
 */
class EntryAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Obtiene los detalles de asientos asociados a esta cuenta contable.
     *
     * @return HasMany
     */
    public function entryDetails()
    {
        return $this->hasMany(EntryDetail::class);
    }
}
