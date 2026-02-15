# Arma Battles Chat - Monorepo Structure

Detta är ett komplett monorepo för Arma Battles Chat-plattformen med backend, frontend och desktop-app.

## 📁 Struktur

```
revolt/
├── arma-backend/          # Rust-baserad backend (Revolt Delta fork)
│   ├── crates/
│   │   ├── core/         # Core functionality
│   │   └── delta/        # API server
│   ├── Cargo.toml
│   └── README.md
│
├── arma-frontend/         # React/TypeScript frontend (Revolt fork)
│   ├── src/
│   │   ├── pages/
│   │   ├── components/
│   │   └── mobx/
│   ├── external/
│   │   ├── components/   # UI component library
│   │   └── lang/         # Translations
│   ├── package.json
│   └── vite.config.ts
│
├── desktop-app/           # Electron desktop app för Windows/Mac/Linux
│   ├── src/
│   │   ├── main.ts       # Electron main process
│   │   ├── native/       # Native integrations
│   │   └── world/        # Isolated contexts
│   ├── assets/
│   ├── forge.config.ts
│   ├── package.json
│   ├── ArmaBattlesChat.sln
│   └── START_VISUAL_STUDIO.md
│
├── .gitignore            # Unified gitignore för hela monorepo
├── TODO.md               # Projektets TODO-lista
└── MONOREPO_STRUCTURE.md # Denna fil
```

---

## 🚀 Kom Igång

### Backend (Rust)

```bash
cd arma-backend

# Installera Rust (om inte redan installerat)
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh

# Bygg och kör
cargo build --release
cargo run --bin delta
```

**Kräver:**
- Rust 1.70+
- MongoDB (för databas)
- Redis (för caching)

**Dokumentation:** `arma-backend/README.md`

---

### Frontend (React/TypeScript)

```bash
cd arma-frontend

# Installera dependencies
pnpm install

# Starta dev server
pnpm dev

# Bygg för produktion
pnpm build
```

**Kräver:**
- Node.js 18+
- pnpm (`corepack enable`)

**Dev server:** http://localhost:5173
**Dokumentation:** `arma-frontend/README.md`

---

### Desktop App (Electron)

```bash
cd desktop-app

# Installera dependencies
pnpm install

# Starta i dev mode
pnpm start

# Bygg Windows installer
pnpm make
```

**Kräver:**
- Node.js 18+
- pnpm
- Visual Studio 2022 (för Windows development)

**Visual Studio Guide:** `desktop-app/START_VISUAL_STUDIO.md`

---

## 🔧 Development Workflow

### Full Stack Development

1. **Backend:** Kör backend på port 8000
   ```bash
   cd arma-backend && cargo run
   ```

2. **Frontend:** Kör frontend dev server på port 5173
   ```bash
   cd arma-frontend && pnpm dev
   ```

3. **Desktop App:** Kör desktop app mot lokal dev server
   ```bash
   cd desktop-app && pnpm start -- --force-server http://localhost:5173
   ```

### Endast Frontend Development

Om backend redan körs på `chat.armabattles.com`:

```bash
cd arma-frontend
pnpm dev
# Ändra API URL i .env till https://api.armabattles.com
```

### Endast Desktop App Development

Om frontend redan är deployed på `chat.armabattles.com`:

```bash
cd desktop-app
pnpm start
# Appen connectar automatiskt till https://chat.armabattles.com
```

---

## 📦 Build för Produktion

### Backend

```bash
cd arma-backend
cargo build --release
# Binary: target/release/delta
```

### Frontend

```bash
cd arma-frontend
pnpm build
# Output: arma-frontend/dist/
```

### Desktop App

```bash
cd desktop-app
pnpm make
# Windows installer: desktop-app/out/make/squirrel.windows/x64/
```

---

## 🎯 Teknologier

| Komponent | Stack |
|-----------|-------|
| **Backend** | Rust, Rocket, MongoDB, Redis |
| **Frontend** | React, TypeScript, MobX, Vite |
| **Desktop** | Electron, TypeScript, Electron Forge |
| **UI Library** | Custom React components |
| **Build Tools** | Cargo, pnpm, Vite, Electron Forge |

---

## 🔗 Viktiga Länkar

- **Produktion Frontend:** https://chat.armabattles.com
- **Produktion API:** https://api.armabattles.com
- **GitHub Repo:** https://github.com/mkungen89/ArmaBattles-Coms

---

## 📝 Commits och Git

### Commit Format

```
type(scope): subject

body (optional)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

**Types:**
- `feat:` - Ny feature
- `fix:` - Buggfix
- `refactor:` - Kodrefaktorering
- `docs:` - Dokumentation
- `chore:` - Maintenance tasks

**Scopes:**
- `backend` - Backend changes
- `frontend` - Frontend changes
- `desktop` - Desktop app changes
- `monorepo` - Monorepo-wide changes

### Branching

- `main` - Production-ready kod
- `develop` - Development branch (om används)
- `feature/*` - Feature branches
- `fix/*` - Bugfix branches

---

## 🧪 Testing

### Backend Tests

```bash
cd arma-backend
cargo test
```

### Frontend Tests

```bash
cd arma-frontend
pnpm test
```

### Desktop App Tests

```bash
cd desktop-app
pnpm test  # Om test suite finns
```

---

## 🔐 Environment Variables

### Backend (.env)

```bash
MONGODB_URI=mongodb://localhost:27017/armabattles
REDIS_URI=redis://localhost:6379
REVOLT_PUBLIC_URL=https://chat.armabattles.com
REVOLT_APP_URL=https://chat.armabattles.com
```

### Frontend (.env)

```bash
VITE_API_URL=https://api.armabattles.com
VITE_ENABLE_VOICE=false
```

### Desktop App

Ingen .env krävs - konfigureras via `forge.config.ts`

---

## 📊 Status

| Komponent | Status | Version |
|-----------|--------|---------|
| Backend | ✅ Funktionell | 1.0.0 |
| Frontend | ✅ Funktionell | 1.0.0 |
| Desktop App | 🚧 Setup klar | 1.0.0 |

---

**Senast uppdaterad:** 2026-02-15
