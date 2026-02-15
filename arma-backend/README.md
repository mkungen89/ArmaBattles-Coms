<div align="center">
<h1>Arma Battles Chat - Backend</h1>

[![Website](https://img.shields.io/badge/Website-armabattles.com-4A90E2?style=flat-square)](https://armabattles.com)
[![Chat](https://img.shields.io/badge/Chat-chat.armabattles.com-2C5F2D?style=flat-square)](https://chat.armabattles.com)
[![License](https://img.shields.io/badge/License-AGPL--3.0-blue?style=flat-square)](LICENSE)

**Real-time chat platform for the Arma Battles gaming community**

</div>

## 🎮 About

This is the backend server for Arma Battles Chat, a real-time chat platform built specifically for the Arma Battles gaming community with integrated OAuth authentication.

**Main Features:**
- 🔐 OAuth integration with [armabattles.com](https://armabattles.com)
- 💬 Real-time chat with WebSocket support
- 📁 File sharing and media support
- 🎨 Custom Arma Battles branding
- 🔒 Secure, self-hosted infrastructure

**Links:**
- 🌐 Main Site: [armabattles.com](https://armabattles.com)
- 💬 Chat: [chat.armabattles.com](https://chat.armabattles.com)

---

## 🚀 Quick Start

### Prerequisites

- **Rust** 1.86.0 or higher
- **Docker** (for databases and services)
- **Git**

### Development Setup

```bash
# Clone this repository
git clone <your-repo-url> arma-backend
cd arma-backend

# Start required services (MongoDB, Redis, MinIO, etc.)
docker compose up -d

# Build the project
cargo build

# Run all services
./scripts/start.sh

# Or run individually:
cargo run --bin revolt-delta      # REST API (port 14702)
cargo run --bin revolt-bonfire    # WebSocket (port 14703)
cargo run --bin revolt-autumn     # File server (port 14704)
cargo run --bin revolt-january    # Proxy (port 14705)
cargo run --bin revolt-gifbox     # Tenor proxy (port 14706)
```

### Configuration

The project uses `Revolt.toml` for development configuration. For custom settings, create a `Revolt.overrides.toml` file.

For **production deployment**, use `Revolt.production.toml` (see below).

---

## 🌍 Production Configuration

### Revolt.production.toml

```toml
[application]
app_name = "Arma Battles Chat"
app_url = "https://chat.armabattles.com"

[hosts]
app = "https://chat.armabattles.com"
api = "https://chat.armabattles.com/api"
events = "wss://chat.armabattles.com/ws"

[api.oauth]
provider_name = "Arma Battles"
provider_url = "https://armabattles.com"
authorize_endpoint = "https://armabattles.com/oauth/authorize"
token_endpoint = "https://armabattles.com/oauth/token"
userinfo_endpoint = "https://armabattles.com/oauth/user"
client_id = "019c5d06-b3f3-709a-a212-b4441d609080"
redirect_uri = "https://chat.armabattles.com/auth/callback"

[database]
mongodb = "mongodb://localhost:27017/armabattles"
redis = "redis://localhost:6379/"

[sentry]
# Optional: Add your Sentry DSN for error tracking
# api = "https://..."
```

---

## 🐳 Docker Deployment

### Building the Image

```bash
# Build for production
docker build -t armabattles/chat-backend:latest .

# Tag and push to Docker Hub (if needed)
docker tag armabattles/chat-backend:latest <your-dockerhub>/chat-backend:latest
docker push <your-dockerhub>/chat-backend:latest
```

### Running with Docker Compose

See the included `compose.yml` for a complete setup with all required services.

---

## 📦 Service Architecture

The Arma Battles Chat backend consists of multiple Rust crates:

| Service                   | Port  | Description                        |
| ------------------------- | :---: | ---------------------------------- |
| `delta`                   | 14702 | REST API server                    |
| `bonfire`                 | 14703 | WebSocket events server            |
| `autumn`                  | 14704 | File server                        |
| `january`                 | 14705 | Proxy server                       |
| `gifbox`                  | 14706 | Tenor proxy server                 |
| MongoDB                   | 27017 | Database                           |
| Redis                     | 6379  | Cache and presence                 |
| MinIO                     | 14009 | S3-compatible file storage         |

### Core Crates

- `core/config` - Configuration management
- `core/database` - MongoDB integration
- `core/files` - S3 and encryption
- `core/models` - API models
- `core/permissions` - Permission logic
- `core/presence` - User presence tracking

---

## 🔧 OAuth Integration

Arma Battles Chat uses OAuth 2.0 to authenticate users from the main [armabattles.com](https://armabattles.com) website.

**OAuth Flow:**
1. User clicks "Login with Arma Battles" on the chat frontend
2. Redirected to `https://armabattles.com/oauth/authorize`
3. User approves the authorization
4. Redirected back to `https://chat.armabattles.com/auth/callback`
5. Backend exchanges code for access token
6. User info fetched from `https://armabattles.com/oauth/user`
7. User session created

**Configuration:**
- Client ID: `019c5d06-b3f3-709a-a212-b4441d609080`
- Redirect URI: `https://chat.armabattles.com/auth/callback`
- Provider: Arma Battles

---

## 📚 Development Ports

When running locally, these ports are used:

| Service                   |      Port      |
| ------------------------- | :------------: |
| MongoDB                   |     27017      |
| Redis                     |      6379      |
| MinIO                     |     14009      |
| Maildev                   | 14025<br>14080 |
| RabbitMQ                  | 5672<br>15672  |
| `delta` (API)             |     14702      |
| `bonfire` (WebSocket)     |     14703      |
| `autumn` (Files)          |     14704      |
| `january` (Proxy)         |     14705      |
| `gifbox` (Tenor)          |     14706      |

---

## 🧪 Testing

```bash
# Start test databases
docker compose -f docker-compose.db.yml up -d

# Run tests
TEST_DB=MONGODB cargo nextest run
```

---

## 📖 Further Documentation

For comprehensive deployment guides, see [DEPLOYMENT.md](DEPLOYMENT.md).

---

## 📝 License

This project is licensed under the **GNU Affero General Public License v3.0** (AGPL-3.0).

Individual crates may supply their own licenses.

---

## 🎨 Branding

**Arma Battles Chat**
- Primary Color: `#4A90E2` (Blue)
- Secondary Color: `#2C5F2D` (Green)
- Main Site: [armabattles.com](https://armabattles.com)
- Chat Site: [chat.armabattles.com](https://chat.armabattles.com)

---

<div align="center">
Built with ❤️ for the Arma Battles community
</div>
