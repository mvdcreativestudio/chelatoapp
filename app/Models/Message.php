<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Message extends Model {

    protected $primaryKey = 'message_id';

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = ['from_phone_id', 'to_phone_id', 'message_text', 'message_source', 'message_created', 'message_type', 'image_url', 'audio_url', 'document_url', 'video_url', 'sticker_url'];

    protected $guarded = ['message_id'];

    const CREATED_AT = 'message_created';
    const UPDATED_AT = 'message_updated';

    /**
     * Obtiene el remitente del mensaje
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(PhoneNumber::class, 'from_phone_id', 'phone_id');
    }

    /**
     * Obtiene el receptor del mensaje
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(PhoneNumber::class, 'to_phone_id', 'phone_id');
    }
}
