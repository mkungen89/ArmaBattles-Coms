<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthAuthorizationCode extends Model
{
    protected $table = 'oauth_authorization_codes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'client_id',
        'scopes',
        'code',
        'redirect_uri',
        'expires_at',
        'revoked',
    ];

    protected $casts = [
        'scopes' => 'array',
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    protected $hidden = [
        'code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(OAuthClient::class, 'client_id');
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
