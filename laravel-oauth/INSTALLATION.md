# 🔐 Laravel OAuth Installation Guide

Komplett guide för att installera OAuth 2.0 authentication på armabattles.com Laravel-sidan.

## 📦 Filer som skapades

```
laravel-oauth/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── OAuthController.php         # Alla OAuth endpoints
│   │   └── Middleware/
│   │       └── OAuthAuthenticate.php       # Token validation middleware
│   └── Models/
│       ├── OAuthClient.php                 # OAuth clients
│       ├── OAuthAccessToken.php            # Access tokens
│       ├── OAuthAuthorizationCode.php      # Authorization codes
│       └── OAuthRefreshToken.php           # Refresh tokens
├── database/
│   ├── migrations/
│   │   └── 2024_01_01_000001_create_oauth_tables.php
│   └── seeders/
│       └── OAuthClientSeeder.php           # Creates chat client
├── resources/
│   └── views/
│       └── oauth/
│           └── authorize.blade.php         # Authorization page
└── routes/
    └── oauth.php                           # OAuth routes
```

## 🚀 Installation Steps

### 1. Kopiera filer till Laravel projekt (5 min)

```bash
# På din armabattles.com server/projekt:
cd /path/to/armabattles.com

# Kopiera Models
cp /path/to/laravel-oauth/app/Models/* app/Models/

# Kopiera Controller
cp /path/to/laravel-oauth/app/Http/Controllers/OAuthController.php app/Http/Controllers/

# Kopiera Middleware
cp /path/to/laravel-oauth/app/Http/Middleware/OAuthAuthenticate.php app/Http/Middleware/

# Kopiera Migration
cp /path/to/laravel-oauth/database/migrations/2024_01_01_000001_create_oauth_tables.php database/migrations/

# Kopiera Seeder
cp /path/to/laravel-oauth/database/seeders/OAuthClientSeeder.php database/seeders/

# Kopiera View
mkdir -p resources/views/oauth
cp /path/to/laravel-oauth/resources/views/oauth/authorize.blade.php resources/views/oauth/

# Kopiera Routes
cp /path/to/laravel-oauth/routes/oauth.php routes/
```

### 2. Registrera Routes (2 min)

Öppna `routes/web.php` och lägg till längst upp:

```php
<?php

// Add this line at the top
require __DIR__.'/oauth.php';

// ... rest of your routes
```

### 3. Registrera Middleware (2 min)

Öppna `app/Http/Kernel.php` (Laravel 10) eller `bootstrap/app.php` (Laravel 11):

**Laravel 10:**
```php
// app/Http/Kernel.php

protected $middlewareAliases = [
    // ... existing middleware
    'oauth' => \App\Http\Middleware\OAuthAuthenticate::class,
];
```

**Laravel 11:**
```php
// bootstrap/app.php

use App\Http\Middleware\OAuthAuthenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'oauth' => OAuthAuthenticate::class,
        ]);
    })
    // ... rest of config
```

### 4. Run Migration (2 min)

```bash
# Skapa OAuth tables i databasen
php artisan migrate
```

### 5. Seed OAuth Client (2 min)

```bash
# Skapa Arma Battles Chat client
php artisan db:seed --class=OAuthClientSeeder
```

**VIKTIGT:** Kopiera Client Secret som visas! Du kommer behöva det för `.env.prod` på chat-servern.

Output ser ut så här:
```
OAuth Client Created!
Client ID: 019c5d06-b3f3-709a-a212-b4441d609080
Client Secret: abc123...xyz789
⚠️  SAVE THIS SECRET NOW - IT WILL NOT BE SHOWN AGAIN!
Add this to your chat .env.prod:
OAUTH_CLIENT_SECRET=abc123...xyz789
```

### 6. Test OAuth Endpoints (5 min)

```bash
# Test authorization endpoint
curl https://armabattles.com/oauth/authorize?client_id=019c5d06-b3f3-709a-a212-b4441d609080&redirect_uri=https://chat.armabattles.com/auth/callback&response_type=code&scope=profile%20email

# Should redirect to login or show authorization page
```

## ✅ Verification Checklist

Efter installation, verifiera att allt fungerar:

- [ ] Routes finns: `php artisan route:list | grep oauth`
  ```
  GET     /oauth/authorize
  POST    /oauth/authorize
  POST    /oauth/token
  GET     /oauth/user
  POST    /oauth/revoke
  ```

- [ ] Tables skapade: `php artisan tinker`
  ```php
  \App\Models\OAuthClient::count();  // Should return 1
  ```

- [ ] Client finns:
  ```php
  $client = \App\Models\OAuthClient::find('019c5d06-b3f3-709a-a212-b4441d609080');
  echo $client->name;  // Should print "Arma Battles Chat"
  ```

- [ ] Authorization page funkar:
  Gå till: `https://armabattles.com/oauth/authorize?client_id=019c5d06-b3f3-709a-a212-b4441d609080&redirect_uri=https://chat.armabattles.com/auth/callback&response_type=code&scope=profile%20email`

## 🔧 Configuration

### User Model

Se till att din User model har dessa fält:
```php
// app/Models/User.php

protected $fillable = [
    'name',
    'email',
    'password',
    'username',  // Optional
    'avatar',    // Optional
];
```

### CORS (viktigt för API requests)

Om du använder Laravel Sanctum eller liknande, se till att CORS tillåter requests från chat.armabattles.com:

```php
// config/cors.php

'paths' => ['api/*', 'oauth/*'],

'allowed_origins' => [
    'https://chat.armabattles.com',
    'http://local.revolt.chat:3000', // Development
],
```

### Session Configuration

För OAuth flow, se till sessions är konfigurerade:

```php
// config/session.php

'domain' => '.armabattles.com',  // Allow subdomains
'secure' => true,                // HTTPS only in production
'same_site' => 'lax',            // Allow OAuth redirects
```

## 🧪 Testing OAuth Flow

### Manual Test

1. **Start Authorization:**
   ```
   https://armabattles.com/oauth/authorize?client_id=019c5d06-b3f3-709a-a212-b4441d609080&redirect_uri=https://chat.armabattles.com/auth/callback&response_type=code&scope=profile%20email&state=random123
   ```

2. **Login** (if not logged in)

3. **Approve Authorization** → Redirects till:
   ```
   https://chat.armabattles.com/auth/callback?code=abc123...&state=random123
   ```

4. **Exchange Code for Token:**
   ```bash
   curl -X POST https://armabattles.com/oauth/token \
     -H "Content-Type: application/json" \
     -d '{
       "grant_type": "authorization_code",
       "client_id": "019c5d06-b3f3-709a-a212-b4441d609080",
       "client_secret": "YOUR_CLIENT_SECRET",
       "code": "abc123...",
       "redirect_uri": "https://chat.armabattles.com/auth/callback"
     }'
   ```

5. **Get User Info:**
   ```bash
   curl https://armabattles.com/oauth/user \
     -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
   ```

### Expected Responses

**Token Response:**
```json
{
  "access_token": "abc123...",
  "token_type": "Bearer",
  "expires_in": 2592000,
  "refresh_token": "xyz789...",
  "scope": "profile email"
}
```

**User Info Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "email_verified": true,
  "avatar": "https://..."
}
```

## 🔒 Security Best Practices

### 1. Protect Client Secret
```bash
# NEVER commit client secret to git
# Add to .env:
echo "OAUTH_CHAT_SECRET=your_secret_here" >> .env
```

### 2. Rate Limiting

Lägg till rate limiting på OAuth routes:

```php
// routes/oauth.php

Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/token', [OAuthController::class, 'issueToken']);
});
```

### 3. HTTPS Only

```php
// app/Providers/AppServiceProvider.php

public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### 4. Token Cleanup

Skapa scheduled job för att rensa gamla tokens:

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        // Delete expired tokens older than 7 days
        OAuthAccessToken::where('expires_at', '<', now()->subDays(7))->delete();
        OAuthAuthorizationCode::where('expires_at', '<', now()->subDays(1))->delete();
    })->daily();
}
```

## 🐛 Troubleshooting

### "Class OAuthClient not found"
```bash
composer dump-autoload
```

### "Table oauth_clients doesn't exist"
```bash
php artisan migrate:fresh
php artisan db:seed --class=OAuthClientSeeder
```

### "Invalid redirect_uri"
Check that redirect URI exactly matches:
```php
$client = OAuthClient::find('019c5d06-b3f3-709a-a212-b4441d609080');
dd($client->redirect_uris);  // Should include chat.armabattles.com/auth/callback
```

### "CORS error"
```bash
# Install Laravel CORS
composer require fruitcake/laravel-cors

# Publish config
php artisan vendor:publish --tag="cors"
```

## 📝 Next Steps

1. ✅ **Save Client Secret** - Add to chat `.env.prod`
2. ✅ **Test OAuth Flow** - Try logging in from chat
3. ✅ **Setup HTTPS** - Use Cloudflare or Let's Encrypt
4. ✅ **Monitor Tokens** - Check database for created tokens
5. ✅ **Setup Cleanup Job** - Auto-delete expired tokens

## 🎉 Done!

Din Laravel OAuth är nu klar! Chatten kan nu autentisera användare via armabattles.com.

**Nästa steg:** Kör `./deploy.sh` på chat VPS och testa inloggning!
