<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthRefreshToken extends Model
{
    protected $table = 'oauth_refresh_tokens';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'access_token_id',
        'token',
        'expires_at',
        'revoked',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    protected $hidden = [
        'token',
    ];

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(OAuthAccessToken::class, 'access_token_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->revoked && !$this->isExpired();
    }
}
