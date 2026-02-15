# OAuth Development Setup Guide

## Problem
OAuth integration med armabattles.com fungerar inte i lokal utvecklingsmiljö eftersom:
1. Laravel (armabattles.com) körs på en VPS/server
2. Chat-plattformen körs lokalt på din dator (localhost)
3. OAuth redirect URIs måste matcha exakt
4. Laravel kan inte nå din localhost från internet

## Lösningar

### 🎯 Lösning 1: Mock OAuth (Snabbast för utveckling)

**Fördelar:**
- Ingen konfiguration av Laravel krävs
- Fungerar omedelbart
- Perfekt för UI/feature-utveckling

**Nackdelar:**
- Inte "riktigt" OAuth
- Testar inte OAuth-flödet

**Implementation:**

1. **Skapa mock OAuth endpoint i backend:**

```rust
// I arma-backend - lägg till ett development-only endpoint
#[get("/auth/dev-login?<username>")]
async fn dev_login(username: String) -> Result<Json<Session>> {
    // Endast i development mode!
    if cfg!(debug_assertions) {
        // Skapa en fake session för användaren
        let session = create_fake_session(&username).await?;
        Ok(Json(session))
    } else {
        Err(Error::Disabled)
    }
}
```

2. **Uppdatera frontend för dev mode:**

```typescript
// I arma-frontend
if (import.meta.env.DEV) {
    // Visa "Dev Login" knapp istället för OAuth
    <button onClick={() => loginDev("testuser")}>
        Dev Login (Local Only)
    </button>
}
```

---

### 🌐 Lösning 2: Ngrok/Tunneling (Mest realistisk)

**Fördelar:**
- Testar riktig OAuth-integration
- Laravel kan nå din lokala chat-plattform
- Exakt samma som produktion

**Nackdelar:**
- Kräver ngrok eller liknande
- URL ändras varje gång (gratis ngrok)

**Implementation:**

1. **Installera ngrok:**
```bash
# Windows
choco install ngrok
# eller ladda ner från https://ngrok.com/
```

2. **Starta ngrok tunnel:**
```bash
# Tunnel för frontend (port 3000)
ngrok http 3000
# Output: https://abc123.ngrok.io -> http://localhost:3000
```

3. **Uppdatera OAuth-konfiguration i Laravel:**
```php
// I Laravel på armabattles.com
'redirect_uri' => 'https://abc123.ngrok.io/auth/callback',
```

4. **Uppdatera frontend .env:**
```bash
VITE_OAUTH_REDIRECT_URI=https://abc123.ngrok.io/auth/callback
```

5. **Starta backend och frontend med ngrok URL**

**Problem:** Ngrok gratis-URL ändras varje gång → måste uppdatera Laravel config varje gång!

---

### 🏠 Lösning 3: Laravel Localhost (Kör Laravel lokalt)

**Fördelar:**
- Allt körs lokalt
- Ingen tunneling krävs
- Snabbast för både Laravel och Chat-utveckling

**Nackdelar:**
- Måste klona armabattles.com-projektet lokalt
- Kräver lokal PHP/MySQL setup

**Implementation:**

1. **Klona Laravel-projekt lokalt:**
```bash
cd C:\projects
git clone <armabattles-repo> armabattles
cd armabattles
composer install
php artisan serve --port=8000
```

2. **Skapa OAuth-endpoints i Laravel:**
```php
// routes/web.php
Route::get('/oauth/authorize', [OAuthController::class, 'authorize']);
Route::post('/oauth/token', [OAuthController::class, 'token']);
Route::get('/oauth/user', [OAuthController::class, 'user'])
    ->middleware('auth:api');
```

3. **Uppdatera backend config:**

Skapa `C:\revolt\arma-backend\Revolt.dev.toml`:
```toml
# Development OAuth Configuration
[api.security.authifier]
oauth_providers = [
    {
        name = "Arma Battles (Local)",
        id = "armabattles",
        enabled = true,
        client_id = "dev-client-id",
        client_secret = "dev-client-secret",
        authorize_url = "http://localhost:8000/oauth/authorize",
        token_url = "http://localhost:8000/oauth/token",
        user_info_url = "http://localhost:8000/oauth/user",
        scopes = ["profile", "email"],
    }
]
```

4. **Uppdatera frontend .env.development:**
```bash
VITE_OAUTH_ENABLED=true
VITE_OAUTH_PROVIDER_NAME=Arma Battles (Local)
VITE_OAUTH_CLIENT_ID=dev-client-id
VITE_OAUTH_REDIRECT_URI=http://localhost:3000/auth/callback
VITE_OAUTH_AUTHORIZE_URL=http://localhost:8000/oauth/authorize
```

---

### 🔐 Lösning 4: Dubbla OAuth-konfigurationer

**Fördelar:**
- Production och Development configs separata
- Kan växla mellan prod och dev OAuth
- Bästa av båda världar

**Implementation:**

**Backend config:**

`Revolt.dev.toml` (för localhost Laravel):
```toml
[api.security.authifier]
oauth_providers = [
    {
        name = "Arma Battles Dev",
        id = "armabattles-dev",
        enabled = true,
        client_id = "dev-client-id",
        client_secret = "dev-secret",
        authorize_url = "http://localhost:8000/oauth/authorize",
        token_url = "http://localhost:8000/oauth/token",
        user_info_url = "http://localhost:8000/oauth/user",
        scopes = ["profile", "email"],
    }
]
```

`Revolt.production.toml` (för prod armabattles.com):
```toml
[api.security.authifier]
oauth_providers = [
    {
        name = "Arma Battles",
        id = "armabattles",
        enabled = true,
        client_id = "019c5d06-b3f3-709a-a212-b4441d609080",
        client_secret = "${OAUTH_CLIENT_SECRET}",
        authorize_url = "https://armabattles.com/oauth/authorize",
        token_url = "https://armabattles.com/oauth/token",
        user_info_url = "https://armabattles.com/oauth/user",
        scopes = ["profile", "email"],
    }
]
```

**Kör backend med rätt config:**
```bash
# Development
export REVOLT_CONFIG=Revolt.dev.toml
cargo run

# Production
export REVOLT_CONFIG=Revolt.production.toml
cargo run
```

---

## 🎯 Rekommenderad Lösning

För **lokal utveckling**, använd **Lösning 3 + 4**:

### Steg-för-steg:

1. **Skapa minimal Laravel OAuth lokalt** (se Laravel setup nedan)
2. **Dubbla configs** för dev/prod
3. **Testa OAuth-flödet lokalt**
4. **När allt fungerar** → Deploy till produktion

### Laravel OAuth Minimal Setup

```php
<?php
// app/Http/Controllers/OAuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Passport\Passport; // eller använd Laravel Sanctum

class OAuthController extends Controller
{
    // GET /oauth/authorize
    public function authorize(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'redirect_uri' => 'required|url',
            'response_type' => 'required|in:code',
            'state' => 'required',
            'scope' => 'nullable',
        ]);

        // Visa authorization page
        return view('oauth.authorize', [
            'client_id' => $request->client_id,
            'redirect_uri' => $request->redirect_uri,
            'state' => $request->state,
            'scopes' => explode(' ', $request->scope ?? ''),
        ]);
    }

    // POST /oauth/authorize (user accepts)
    public function authorizeAccept(Request $request)
    {
        // Generate authorization code
        $code = bin2hex(random_bytes(32));

        // Store code in cache/database
        cache()->put("oauth_code:$code", [
            'user_id' => auth()->id(),
            'client_id' => $request->client_id,
            'redirect_uri' => $request->redirect_uri,
            'expires_at' => now()->addMinutes(5),
        ], 300);

        // Redirect back with code
        return redirect($request->redirect_uri . '?' . http_build_query([
            'code' => $code,
            'state' => $request->state,
        ]));
    }

    // POST /oauth/token
    public function token(Request $request)
    {
        $request->validate([
            'grant_type' => 'required|in:authorization_code',
            'code' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
            'redirect_uri' => 'required',
        ]);

        // Verify code
        $codeData = cache()->get("oauth_code:{$request->code}");

        if (!$codeData || $codeData['client_id'] !== $request->client_id) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        // Verify client secret
        if ($request->client_secret !== config('oauth.clients.dev-client-id.secret')) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        // Delete used code
        cache()->forget("oauth_code:{$request->code}");

        // Generate access token
        $accessToken = bin2hex(random_bytes(32));

        // Store token
        cache()->put("oauth_token:$accessToken", [
            'user_id' => $codeData['user_id'],
            'client_id' => $request->client_id,
        ], 3600); // 1 hour

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
    }

    // GET /oauth/user
    public function user(Request $request)
    {
        $token = $request->bearerToken();

        $tokenData = cache()->get("oauth_token:$token");

        if (!$tokenData) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $user = User::find($tokenData['user_id']);

        return response()->json([
            'id' => (string) $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'avatar' => $user->avatar_url ?? null,
        ]);
    }
}
```

**Routes:**
```php
// routes/web.php
Route::middleware('web')->group(function () {
    Route::get('/oauth/authorize', [OAuthController::class, 'authorize']);
    Route::post('/oauth/authorize', [OAuthController::class, 'authorizeAccept']);
});

// routes/api.php
Route::post('/oauth/token', [OAuthController::class, 'token']);
Route::get('/oauth/user', [OAuthController::class, 'user']);
```

**Config:**
```php
// config/oauth.php
return [
    'clients' => [
        'dev-client-id' => [
            'secret' => 'dev-client-secret',
            'redirect' => 'http://localhost:3000/auth/callback',
        ],
    ],
];
```

---

## ✅ Snabbstart: Mock Development

Om du bara vill testa UI utan OAuth:

**1. Skapa override config:**

`C:\revolt\arma-backend\Revolt.overrides.toml`:
```toml
# Disable OAuth requirement in development
[api.security]
require_oauth = false
```

**2. Uppdatera frontend .env:**
```bash
VITE_OAUTH_ENABLED=false
```

**3. Starta med dev mode:**
```bash
# Backend
cd arma-backend
cargo run

# Frontend
cd arma-frontend
yarn dev
```

Nu kan du logga in med email/password istället för OAuth!

---

## 📝 Sammanfattning

| Lösning | Tid | Komplexitet | Realism |
|---------|-----|-------------|---------|
| Mock OAuth | 5 min | Låg | Låg |
| Ngrok Tunnel | 15 min | Medel | Hög |
| Laravel Localhost | 1-2 timmar | Hög | Hög |
| Dubbla Configs | 30 min | Medel | Hög |

**Min rekommendation:**
1. Börja med **Mock/Disabled OAuth** för att testa UI
2. Sätt upp **Laravel Localhost** när du ska testa OAuth-flödet
3. Deploy till **Produktion** när allt fungerar lokalt

---

**Vill du ha hjälp med någon av dessa lösningar?**
