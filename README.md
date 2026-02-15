# Arma Battles Chat Platform

<div align="center">

[![Website](https://img.shields.io/badge/Website-armabattles.com-4A90E2?style=flat-square)](https://armabattles.com)
[![Chat](https://img.shields.io/badge/Chat-chat.armabattles.com-2C5F2D?style=flat-square)](https://chat.armabattles.com)

**Complete real-time chat solution for the Arma Battles gaming community**

</div>

---

## 📦 Project Structure

This repository contains the complete Arma Battles Chat platform:

```
arma-battles-chat/
├── arma-backend/     # Backend server (Rust)
├── arma-frontend/    # Frontend web client (React/Preact)
└── README.md         # This file
```

### 🔧 arma-backend

Rust-based backend server providing:
- REST API server
- WebSocket real-time messaging
- File upload/download service
- User authentication with OAuth
- Database management (MongoDB, Redis)

**Documentation:** See [arma-backend/README.md](arma-backend/README.md)

### 🎨 arma-frontend

React/Preact-based web client providing:
- Modern chat interface
- Real-time messaging
- File sharing and previews
- OAuth "Login with Arma Battles"
- Responsive design

**Documentation:** See [arma-frontend/README.md](arma-frontend/README.md)

---

## 🚀 Quick Start

### Development

**Backend:**
```bash
cd arma-backend
docker compose up -d  # Start services
cargo build
./scripts/start.sh
```

**Frontend:**
```bash
cd arma-frontend
yarn install
yarn build:deps
yarn dev
```

### Production Deployment

See comprehensive deployment guides:
- **Backend:** [arma-backend/DEPLOYMENT.md](arma-backend/DEPLOYMENT.md)
- **Frontend:** [arma-frontend/BUILD.md](arma-frontend/BUILD.md)

---

## 🎨 Branding

**Arma Battles Chat**
- **Primary Color:** `#4A90E2` (Blue)
- **Secondary Color:** `#2C5F2D` (Green)
- **Accent Color:** `#E24A4A` (Red)
- **Main Site:** [armabattles.com](https://armabattles.com)
- **Chat Site:** [chat.armabattles.com](https://chat.armabattles.com)

---

## 🔐 OAuth Integration

The platform integrates with the main Arma Battles website for authentication:

- **Provider:** Arma Battles
- **Authorize URL:** `https://armabattles.com/oauth/authorize`
- **Token URL:** `https://armabattles.com/oauth/token`
- **User Info URL:** `https://armabattles.com/oauth/user`
- **Client ID:** `019c5d06-b3f3-709a-a212-b4441d609080`
- **Redirect URI:** `https://chat.armabattles.com/auth/callback`

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────┐
│   chat.armabattles.com (Nginx)          │
├─────────────────────────────────────────┤
│  /            → Frontend (Static Files) │
│  /api         → Backend (REST API)      │
│  /ws          → WebSocket Server        │
│  /files       → File Server             │
└─────────────────────────────────────────┘
         ↓ OAuth Authentication
┌─────────────────────────────────────────┐
│   armabattles.com (Laravel)             │
│   /oauth      → OAuth Provider          │
└─────────────────────────────────────────┘
```

---

## 🐳 Docker Deployment

### Backend
```bash
cd arma-backend
docker build -t armabattles/chat-backend:latest .
docker run -d -p 14702:14702 armabattles/chat-backend:latest
```

### Frontend
```bash
cd arma-frontend
docker build -t armabattles/chat-frontend:latest .
docker run -d -p 80:80 armabattles/chat-frontend:latest
```

---

## 📝 Tech Stack

### Backend
- **Language:** Rust 1.86+
- **Database:** MongoDB, Redis
- **Storage:** MinIO (S3-compatible)
- **Services:** REST API, WebSocket, File Server

### Frontend
- **Framework:** Preact (React alternative)
- **Build Tool:** Vite
- **Language:** TypeScript
- **State:** MobX
- **Styling:** Styled Components

---

## 📚 Documentation

- **Backend Setup:** [arma-backend/README.md](arma-backend/README.md)
- **Backend Deployment:** [arma-backend/DEPLOYMENT.md](arma-backend/DEPLOYMENT.md)
- **Frontend Setup:** [arma-frontend/README.md](arma-frontend/README.md)
- **Frontend Build:** [arma-frontend/BUILD.md](arma-frontend/BUILD.md)

---

## 📋 Prerequisites

### Backend
- Rust 1.86.0+
- Docker & Docker Compose
- MongoDB 5.0+
- Redis 6.0+

### Frontend
- Node.js 20.x+
- Yarn 3.x (Berry)
- 4GB+ RAM for building

---

## 🔒 Security

- OAuth 2.0 authentication
- HTTPS/WSS in production
- CSRF protection
- Rate limiting
- File upload validation
- Environment-based secrets

---

## 📄 License

This project is licensed under the **GNU Affero General Public License v3.0** (AGPL-3.0).

See [LICENSE](LICENSE) for details.

---

## 🤝 Support

For questions or support, contact the Arma Battles development team.

---

<div align="center">
Built with ❤️ for the Arma Battles community
</div>
