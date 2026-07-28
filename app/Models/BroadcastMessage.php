<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastMessage extends Model
{
    protected $fillable = [
        'title',
        'body',
        'category',
        'priority',
        'sent_by',
        'recipients_count',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
