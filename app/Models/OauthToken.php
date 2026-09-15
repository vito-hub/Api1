<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OauthToken extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'client_id',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token'              => 'encrypted',
            'refresh_token'             => 'encrypted',
            'access_token_expires_at'   => 'datetime',
            'refresh_token_expires_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accessTokenExpired(): bool
    {
        return $this->access_token_expires_at->isPast();
    }

    public function refreshTokenExpired(): bool
    {
        return $this->refresh_token_expires_at?->isPast() ?? false;
    }

}
