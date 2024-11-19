<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoresEmailConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'mail_reply_to_address',
        'mail_reply_to_name',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
