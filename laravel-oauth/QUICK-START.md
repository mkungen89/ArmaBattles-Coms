# ⚡ OAuth Quick Start

## 📋 5 Minuters Setup

### 1. Kopiera filer till armabattles.com Laravel projekt

```bash
cd /path/to/armabattles.com

# Models
cp laravel-oauth/app/Models/* app/Models/

# Controller
cp laravel-oauth/app/Http/Controllers/OAuthController.php app/Http/Controllers/

# Middleware
cp laravel-oauth/app/Http/Middleware/OAuthAuthenticate.php app/Http/Middleware/

# Migration
cp laravel-oauth/database/migrations/2024_01_01_000001_create_oauth_tables.php database/migrations/

# Seeder
cp laravel-oauth/database/seeders/OAuthClientSeeder.php database/seeders/

# View
mkdir -p resources/views/oauth
cp laravel-oauth/resources/views/oauth/authorize.blade.php resources/views/oauth/

# Routes
cp laravel-oauth/routes/oauth.php routes/
```

### 2. Aktivera routes

**routes/web.php:**
```php
<?php
require __DIR__.'/oauth.php';  // <-- Add this line
// ... rest of routes
```

### 3. Registrera middleware

**app/Http/Kernel.php** (Laravel 10):
```php
protected $middlewareAliases = [
    'oauth' => \App\Http\Middleware\OAuthAuthenticate::class,
];
```

**bootstrap/app.php** (Laravel 11):
```php
use App\Http\Middleware\OAuthAuthenticate;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['oauth' => OAuthAuthenticate::class]);
})
```

### 4. Kör migration och seeder

```bash
php artisan migrate
php artisan db:seed --class=OAuthClientSeeder
```

**⚠️ SPARA CLIENT SECRET som visas!**

### 5. Test

```bash
curl https://armabattles.com/oauth/authorize?client_id=019c5d06-b3f3-709a-a212-b4441d609080&redirect_uri=https://chat.armabattles.com/auth/callback&response_type=code
```

## ✅ Klart!

Lägg till Client Secret i chat `.env.prod`:
```env
OAUTH_CLIENT_SECRET=<secret från steg 4>
```

**Full dokumentation:** Se `INSTALLATION.md`
