<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OAuthAccessToken extends Model
{
    protected $table = 'oauth_access_tokens';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'client_id',
        'scopes',
        'token',
        'expires_at',
        'revoked',
    ];

    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    protected $hidden = [
        'token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(OAuthClient::class, 'client_id');
    }

    public function refreshToken(): HasOne
    {
        return $this->hasOne(OAuthRefreshToken::class, 'access_token_id');
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
