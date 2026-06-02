<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageConsent extends Model
{
    protected $fillable = [
        'contact_id',
        'channel',
        'purpose',
        'consented_at',
        'source',
        'ip_address',
        'user_agent',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'contact_id' => 'integer',
            'consented_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}