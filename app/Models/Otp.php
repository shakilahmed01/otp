<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Otp extends Model
{
    protected $table = 'otps';

    protected $fillable = [
        'user_id',
        'channel',
        'target',
        'token',
        'expires_at',
        'used',
        'attempts',
    ];

    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
