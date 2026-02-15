# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Arma Battles Chat is a real-time chat platform for the Arma Battles gaming community. The repository is a monorepo containing both the Rust backend and Preact frontend.

- **Main Site**: https://armabattles.com
- **Chat Site**: https://chat.armabattles.com
- **Architecture**: Microservices backend (Rust) + SPA frontend (Preact/TypeScript)

## Repository Structure

```
revolt/
├── arma-backend/           # Rust backend (workspace with multiple services)
│   ├── crates/
│   │   ├── delta/         # REST API server (port 14702)
│   │   ├── bonfire/       # WebSocket events server (port 14703)
│   │   ├── core/          # Shared libraries
│   │   │   ├── config/    # Configuration management
│   │   │   ├── database/  # MongoDB integration
│   │   │   ├── files/     # S3 file handling & encryption
│   │   │   ├── models/    # API data models
│   │   │   ├── permissions/  # Permission logic
│   │   │   ├── presence/  # User presence tracking
│   │   │   └── ...
│   │   ├── services/
│   │   │   ├── autumn/    # File server (port 14704)
│   │   │   ├── january/   # Proxy server (port 14705)
│   │   │   └── gifbox/    # Tenor proxy (port 14706)
│   │   └── daemons/       # Background tasks
│   ├── Revolt.toml        # Dev configuration
│   └── Cargo.toml         # Workspace definition
│
├── arma-frontend/         # Preact frontend
│   ├── src/
│   │   ├── pages/        # Page components
│   │   ├── components/   # Reusable UI components
│   │   ├── mobx/         # State management (MobX stores)
│   │   ├── lib/          # Utility functions
│   │   ├── context/      # React contexts
│   │   └── controllers/  # Business logic controllers
│   ├── external/         # Git submodules (must be built separately)
│   │   ├── components/   # @revoltchat/ui component library
│   │   ├── revolt.js/    # API client library
│   │   └── lang/         # Localization files
│   ├── vite.config.ts    # Vite build configuration
│   └── package.json      # Dependencies and scripts
│
├── docker-compose.yml     # All services (MongoDB, Redis, MinIO, etc.)
├── start-dev.sh          # Start development environment
├── stop-dev.sh           # Stop development environment
└── DEVELOPMENT.md        # Detailed development guide
```

## Common Development Commands

### Quick Start

```bash
# Start entire development environment
./start-dev.sh

# Stop everything
./stop-dev.sh

# Restart everything
./restart-dev.sh
```

### Backend (Rust)

```bash
cd arma-backend

# Build all services
cargo build

# Run a specific service
cargo run --bin revolt-delta      # REST API
cargo run --bin revolt-bonfire    # WebSocket
cargo run --bin revolt-autumn     # File server
cargo run --bin revolt-january    # Proxy

# Watch mode (auto-rebuild on changes)
cargo install cargo-watch
cargo watch -x 'run --bin revolt-delta'

# Run tests
cargo test

# Quick syntax check (faster than build)
cargo check

# Production build
cargo build --release

# Format code
cargo fmt

# Run linter
cargo clippy
```

### Frontend (Preact)

```bash
cd arma-frontend

# Install dependencies
yarn

# Build external dependencies (MUST run first after clone)
yarn build:deps

# Start development server with hot reload
yarn dev

# Build for production
yarn build

# Build with increased memory (if build fails)
yarn build:highmem

# Preview production build
yarn preview

# Run linter
yarn lint

# Format code
yarn fmt

# Type checking
yarn typecheck

# Serve built files
yarn start
```

### Docker Services

```bash
# Start all required services
docker compose up -d

# Start only specific services
docker compose up -d mongodb redis minio

# Stop all services
docker compose down

# View logs
docker compose logs -f [service-name]

# Restart a service
docker compose restart mongodb
```

## Architecture & Key Concepts

### Backend Architecture

The backend is a **Rust workspace** with multiple microservices:

- **Delta** (port 14702): Main REST API server handling HTTP requests
- **Bonfire** (port 14703): WebSocket server for real-time events
- **Autumn** (port 14704): File upload/download service with S3 integration
- **January** (port 14705): Proxy server for link previews
- **Gifbox** (port 14706): Tenor GIF API proxy

**Core crates** provide shared functionality:
- `core/database`: MongoDB connection pool and queries
- `core/models`: Shared data structures and API models
- `core/permissions`: Permission checking and authorization logic
- `core/files`: S3 file storage abstraction with encryption support
- `core/presence`: Redis-based user presence tracking
- `core/config`: Configuration loading from Revolt.toml

**Configuration**: Uses `Revolt.toml` for development. Create `Revolt.overrides.toml` for local overrides (never commit this file).

**Database**:
- MongoDB (port 27017) - primary database
- Redis (port 6379) - caching and presence
- MinIO (ports 14009, 14010) - S3-compatible file storage

### Frontend Architecture

Built with **Preact** (lightweight React alternative) using:

- **Vite**: Fast build tool with HMR (Hot Module Replacement)
- **TypeScript**: Type safety
- **MobX**: State management (stores in `src/mobx/`)
- **Styled Components**: CSS-in-JS styling
- **revolt.js**: API client library (git submodule)
- **@revoltchat/ui**: UI component library (git submodule)

**State Management**: MobX stores are in `src/mobx/stores/`. The main state is coordinated by `src/mobx/State.ts`.

**External Dependencies**: The frontend depends on git submodules in `external/`. Always run `yarn build:deps` after cloning or updating submodules.

**Development URL**: Uses `local.revolt.chat:3000` (resolves to 127.0.0.1) for proper OAuth flow during development.

### OAuth Integration

The platform integrates with **armabattles.com** for authentication:

- Provider: Arma Battles
- Authorize URL: `https://armabattles.com/oauth/authorize`
- Token URL: `https://armabattles.com/oauth/token`
- User Info URL: `https://armabattles.com/oauth/user`
- Client ID: `019c5d06-b3f3-709a-a212-b4441d609080`
- Redirect URI: `https://chat.armabattles.com/auth/callback`

When working on authentication, test the full OAuth flow by clicking "Login with Arma Battles" in the UI.

## Testing

### Backend Tests

```bash
cd arma-backend

# Run all tests
cargo test

# Run tests for a specific crate
cargo test -p revolt-database

# Run with database integration tests
TEST_DB=MONGODB cargo nextest run

# Run tests in watch mode
cargo watch -x test
```

### Frontend Tests

```bash
cd arma-frontend

# Run tests
yarn test
```

## Adding New Features

### Adding a Backend Endpoint

1. Add route handler in `arma-backend/crates/delta/src/routes/`
2. Add/update models in `arma-backend/crates/core/models/src/`
3. Add database queries in `arma-backend/crates/core/database/src/`
4. Test with: `cargo run --bin revolt-delta` and `curl http://localhost:14702/your-endpoint`

### Adding Frontend Components

1. Create component in `arma-frontend/src/components/` or `arma-frontend/src/pages/`
2. Add to routing if it's a page
3. Use MobX stores from `src/mobx/` for state
4. Hot reload will automatically update the browser

### Modifying Database Schema

1. Update models in `arma-backend/crates/core/models/src/`
2. Update database queries in `arma-backend/crates/core/database/src/`
3. Consider migration strategy for existing data
4. Rebuild and restart backend services

## Service Ports Reference

| Service | Port | Description |
|---------|------|-------------|
| Frontend Dev | 3000 | Vite dev server |
| Delta | 14702 | REST API |
| Bonfire | 14703 | WebSocket |
| Autumn | 14704 | File server |
| January | 14705 | Proxy |
| Gifbox | 14706 | Tenor proxy |
| MongoDB | 27017 | Database |
| Redis | 6379 | Cache |
| MinIO API | 14009 | S3 storage |
| MinIO Console | 14010 | S3 web UI |
| RabbitMQ | 5672 | Message queue |
| RabbitMQ UI | 15672 | Queue management |
| MailDev SMTP | 14025 | Dev mail server |
| MailDev UI | 14080 | Email viewer |

## Important Notes

### Git Submodules

The frontend uses git submodules for external dependencies. After cloning or pulling:

```bash
# If you cloned without --recursive
git submodule init
git submodule update

# After pulling updates
git submodule update

# Always rebuild after submodule updates
cd arma-frontend
yarn build:deps
```

### Memory Issues

Frontend builds may require increased memory:

```bash
# Use the high-memory build
yarn build:highmem

# Or set manually
NODE_OPTIONS='--max-old-space-size=4096' yarn build
```

### Windows Development

The project is developed on Windows. Use bash commands via Git Bash or WSL. The shell scripts (`start-dev.sh`, etc.) are bash scripts.

### Configuration Files

- Backend config: `arma-backend/Revolt.toml` (committed) and `Revolt.overrides.toml` (local only)
- Frontend config: `arma-frontend/.env.local` (development), `.env.production` (production)
- Never commit sensitive data like OAuth secrets or API keys

### Process Management

PIDs for backend services are saved to `.dev-pids` when using `start-dev.sh`. Use `stop-dev.sh` to cleanly stop all services.

## Branding

**Arma Battles Chat** branding:
- Primary Color: `#4A90E2` (Blue)
- Secondary Color: `#2C5F2D` (Green)
- Accent Color: `#E24A4A` (Red)
- Background: `#1A1A1A` (Dark)

## Further Documentation

- Detailed development guide: `DEVELOPMENT.md`
- Backend details: `arma-backend/README.md`
- Frontend details: `arma-frontend/README.md`
- Backend deployment: `arma-backend/DEPLOYMENT.md`
- Frontend build guide: `arma-frontend/BUILD.md`
