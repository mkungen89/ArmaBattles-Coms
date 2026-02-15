<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OAuthClient extends Model
{
    protected $table = 'oauth_clients';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'secret',
        'redirect_uris',
        'revoked',
    ];

    protected $casts = [
        'redirect_uris' => 'array',
        'revoked' => 'boolean',
    ];

    protected $hidden = [
        'secret',
    ];

    public function authorizationCodes(): HasMany
    {
        return $this->hasMany(OAuthAuthorizationCode::class, 'client_id');
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(OAuthAccessToken::class, 'client_id');
    }

    public function validateRedirectUri(string $uri): bool
    {
        return in_array($uri, $this->redirect_uris);
    }

    public function validateSecret(string $secret): bool
    {
        return hash_equals($this->secret, hash('sha256', $secret));
    }
}
