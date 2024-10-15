<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrentAccountInitialCredit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'total_debit',
        'description',
        'due_date',
        'current_account_id',
        'current_account_settings_id',
    ];

    /**
     * Get the current account associated with the initial credit.
     */
    public function currentAccount()
    {
        return $this->belongsTo(CurrentAccount::class);
    }

    /**
     * Get the current account settings associated with the initial credit.
     */
    public function currentAccountSettings()
    {
        return $this->belongsTo(CurrentAccountSettings::class);
    }
}