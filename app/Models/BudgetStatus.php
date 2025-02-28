<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetStatus extends Model {
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'user_id',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     *
     *
     * @return BelongsTo
     */
    public function budget() {
        return $this->belongsTo(Budget::class);
    }

    /**
     * 
     *
     * @return BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
