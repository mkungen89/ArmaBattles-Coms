# Arma Battles Chat - Development Guide

Quick guide for developing Arma Battles Chat locally.

---

## 🚀 Quick Start

### Start Everything
```bash
./start-dev.sh
```

### Stop Everything
```bash
./stop-dev.sh
```

### Restart Everything
```bash
./restart-dev.sh
```

---

## 🔧 Development Workflow

### Backend Development (Rust)

**Location:** `arma-backend/`

#### Quick iteration:
```bash
# Start just one service for testing
cd arma-backend
cargo run --bin revolt-delta

# Watch mode (requires cargo-watch)
cargo install cargo-watch
cargo watch -x 'run --bin revolt-delta'

# Run tests
cargo test

# Check code without building
cargo check
```

#### Rebuilding:
- **Incremental builds** are fast (~30 seconds after first build)
- Only rebuild what changed
- Use `cargo check` for quick syntax validation

### Frontend Development (React/Preact)

**Location:** `arma-frontend/`

#### Development server (with hot reload):
```bash
cd arma-frontend
yarn dev
```

Access at: http://local.revolt.chat:3000

#### Build for production:
```bash
yarn build
```

#### Quick iteration workflow:
1. Edit files in `arma-frontend/src/`
2. Save - HMR (Hot Module Replacement) updates browser automatically
3. No rebuild needed!

---

## 📂 Project Structure

```
arma-battles-chat/
├── arma-backend/          # Rust backend
│   ├── crates/
│   │   ├── delta/        # REST API (port 14702)
│   │   ├── bonfire/      # WebSocket (port 14703)
│   │   ├── autumn/       # Files (port 14704)
│   │   ├── january/      # Proxy (port 14705)
│   │   └── gifbox/       # Tenor (port 14706)
│   └── Revolt.toml       # Dev config
│
├── arma-frontend/         # React/Preact frontend
│   ├── src/
│   │   ├── pages/        # Page components
│   │   ├── components/   # Reusable components
│   │   ├── lib/          # Utilities
│   │   └── main.tsx      # Entry point
│   └── .env.local        # Dev environment vars
│
├── docker-compose.yml     # All services
├── start-dev.sh          # Start dev environment
├── stop-dev.sh           # Stop dev environment
└── restart-dev.sh        # Restart everything
```

---

## 🐳 Docker Services

### Start only database services:
```bash
docker compose up -d mongodb redis minio rabbitmq maildev
```

### Stop all Docker services:
```bash
docker compose down
```

### View logs:
```bash
docker compose logs -f [service-name]
```

### Available services:
- `mongodb` - Database (port 27017)
- `redis` - Cache (port 6379)
- `minio` - S3 storage (ports 14009, 14010)
- `rabbitmq` - Message queue (ports 5672, 15672)
- `maildev` - Email testing (ports 14025, 14080)
- `frontend` - Frontend Docker container (port 3000)

---

## 🔍 Development URLs

| Service | URL | Purpose |
|---------|-----|---------|
| **Frontend Dev** | http://local.revolt.chat:3000 | Development with HMR |
| **Frontend Prod** | http://localhost:3000 | Docker container |
| **API (Delta)** | http://localhost:14702 | REST API |
| **WebSocket (Bonfire)** | ws://localhost:14703 | Real-time events |
| **Files (Autumn)** | http://localhost:14704 | File upload/download |
| **Proxy (January)** | http://localhost:14705 | Link previews |
| **MinIO Console** | http://localhost:14010 | S3 storage UI |
| **RabbitMQ UI** | http://localhost:15672 | Message queue UI |
| **Maildev** | http://localhost:14080 | Email viewer |

---

## 🛠️ Common Tasks

### Add a new frontend feature:

1. Edit files in `arma-frontend/src/`
2. Save - browser auto-reloads
3. Test in browser
4. Commit changes

### Add a new backend endpoint:

1. Edit `arma-backend/crates/delta/src/routes/`
2. Stop Delta: Find PID in `.dev-pids` and `kill [PID]`
3. Restart: `cd arma-backend && cargo run --bin revolt-delta`
4. Test with `curl http://localhost:14702/your-endpoint`

### Update database schema:

1. Edit `arma-backend/crates/core/models/src/`
2. Update `arma-backend/crates/core/database/src/`
3. Rebuild backend
4. Restart services

---

## 🧪 Testing

### Backend tests:
```bash
cd arma-backend
cargo test
```

### Frontend tests:
```bash
cd arma-frontend
yarn test
```

### Integration testing:
1. Start all services with `./start-dev.sh`
2. Open http://localhost:3000
3. Test OAuth flow, messaging, file uploads, etc.

---

## 🐛 Debugging

### Backend logs:
```bash
# View logs from running Rust process
# Check terminal where you started the service

# Or use RUST_LOG for more verbose output:
RUST_LOG=debug cargo run --bin revolt-delta
```

### Frontend logs:
- Open browser DevTools (F12)
- Check Console tab
- Check Network tab for API calls

### Database inspection:
```bash
# MongoDB
docker exec -it armabattles-mongodb mongosh

# Redis
docker exec -it armabattles-redis redis-cli
```

### View emails sent by the system:
Open http://localhost:14080 - all emails appear here

---

## ⚡ Performance Tips

### Faster Rust compilation:
```bash
# Use mold linker (Linux)
mold -run cargo build

# Or use lld (cross-platform)
# Add to .cargo/config.toml:
[target.x86_64-pc-windows-msvc]
rustflags = ["-C", "link-arg=-fuse-ld=lld"]
```

### Faster frontend builds:
```bash
# Use more memory for Vite
NODE_OPTIONS='--max-old-space-size=4096' yarn build
```

### Skip some checks during development:
```bash
# Backend - just check, don't build
cargo check

# Frontend - skip type checking
yarn build --mode development
```

---

## 🔑 Environment Variables

### Backend (.env or Revolt.toml):
```toml
[database]
mongodb = "mongodb://localhost:27017/armabattles"
redis = "redis://localhost:6379/"

[api.oauth]
client_id = "019c5d06-b3f3-709a-a212-b4441d609080"
# client_secret via env var: OAUTH_CLIENT_SECRET
```

### Frontend (.env.local):
```env
VITE_API_URL=http://local.revolt.chat:14702/api
VITE_WS_URL=ws://local.revolt.chat:14703
VITE_APP_TITLE=Arma Battles Chat (Dev)
```

---

## 📚 Useful Commands

### Docker:
```bash
# Restart a service
docker compose restart mongodb

# View resource usage
docker stats

# Clean up everything
docker compose down -v  # WARNING: Deletes volumes!
```

### Git:
```bash
# Create feature branch
git checkout -b feature/my-feature

# Update submodules (frontend)
cd arma-frontend
git submodule update --remote
```

### Cargo (Rust):
```bash
# Update dependencies
cargo update

# Add a new dependency
cargo add tokio

# Build optimized release
cargo build --release
```

---

## 🚨 Troubleshooting

### Port already in use:
```bash
# Find what's using port 14702
lsof -i :14702  # Linux/Mac
netstat -ano | findstr :14702  # Windows

# Kill the process
kill -9 [PID]
```

### Rust compilation fails:
```bash
# Clean and rebuild
cargo clean
cargo build
```

### Frontend won't start:
```bash
# Clear cache and reinstall
rm -rf node_modules .yarn/cache
yarn install
yarn build:deps
```

### Database connection issues:
```bash
# Check if MongoDB is running
docker ps | grep mongodb

# Restart MongoDB
docker compose restart mongodb
```

---

## 🎯 Development Best Practices

1. **Always run tests** before committing
2. **Use feature branches** for new features
3. **Keep dependencies updated** regularly
4. **Document new APIs** in code comments
5. **Test OAuth flow** after backend changes
6. **Check logs** when debugging issues
7. **Use environment variables** for secrets

---

## 📖 Further Reading

- **Backend Documentation:** [arma-backend/README.md](arma-backend/README.md)
- **Frontend Documentation:** [arma-frontend/README.md](arma-frontend/README.md)
- **Deployment Guide:** [arma-backend/DEPLOYMENT.md](arma-backend/DEPLOYMENT.md)
- **Build Guide:** [arma-frontend/BUILD.md](arma-frontend/BUILD.md)

---

**Happy coding! 🚀**
