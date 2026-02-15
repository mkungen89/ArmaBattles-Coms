<div align="center">
<h1>Arma Battles Chat - Frontend</h1>

[![Website](https://img.shields.io/badge/Website-armabattles.com-4A90E2?style=flat-square)](https://armabattles.com)
[![Chat](https://img.shields.io/badge/Chat-chat.armabattles.com-2C5F2D?style=flat-square)](https://chat.armabattles.com)
[![License](https://img.shields.io/badge/License-AGPL--3.0-blue?style=flat-square)](LICENSE)

**Real-time chat frontend for the Arma Battles gaming community**

</div>

## 🎮 About

This is the web frontend for Arma Battles Chat, a real-time chat platform built specifically for the Arma Battles gaming community with custom branding and OAuth integration.

**Main Features:**
- 🔐 OAuth "Login with Arma Battles" button
- 🎨 Custom Arma Battles branding and colors
- 💬 Real-time chat interface
- 📁 File sharing and media preview
- 🔔 Notifications and presence tracking
- 📱 Responsive design for desktop and mobile

**Links:**
- 🌐 Main Site: [armabattles.com](https://armabattles.com)
- 💬 Chat: [chat.armabattles.com](https://chat.armabattles.com)

---

## 🚀 Quick Start

### Prerequisites

- **Node.js** 20.x or higher
- **Yarn** 3.x (Berry)
- **Git**

### Development Setup

```bash
# Clone this repository (with submodules)
git clone --recursive <your-repo-url> arma-frontend
cd arma-frontend

# Install dependencies
yarn

# Build external dependencies (required first time)
yarn build:deps

# Start development server
yarn dev
```

You can now access the client at **http://local.revolt.chat:3000**.

> **Note:** The dev server uses `local.revolt.chat` which resolves to `127.0.0.1`. This is required for proper authentication flow during development.

---

## 📦 Tech Stack

- **[Preact](https://preactjs.com/)** - Fast 3kB React alternative
- **[Vite](https://vitejs.dev/)** - Next generation frontend tooling
- **TypeScript** - Type safety
- **Styled Components** - CSS-in-JS
- **MobX** - State management
- **WebSocket** - Real-time communication

---

## 🎨 Arma Battles Branding

### Colors

- **Primary:** `#4A90E2` (Blue)
- **Secondary:** `#2C5F2D` (Green)
- **Accent:** `#E24A4A` (Red)
- **Background:** `#1A1A1A` (Dark)

### Logo

- **Logo URL:** `https://armabattles.com/images/logo.png`

### OAuth Integration

- **Provider:** Arma Battles
- **Authorize URL:** `https://armabattles.com/oauth/authorize`
- **Token URL:** `https://armabattles.com/oauth/token`
- **User Info URL:** `https://armabattles.com/oauth/user`
- **Client ID:** `019c5d06-b3f3-709a-a212-b4441d609080`
- **Redirect URI:** `https://chat.armabattles.com/auth/callback`

---

## 🔧 Configuration

### Environment Variables

Create a `.env.local` file for development:

```env
# Development configuration
VITE_API_URL=http://local.revolt.chat:14702/api
VITE_WS_URL=ws://local.revolt.chat:14703
VITE_APP_TITLE=Arma Battles Chat (Dev)
VITE_APP_NAME=Arma Battles
```

For **production**, use `.env.production`:

```env
# Production configuration
VITE_API_URL=https://chat.armabattles.com/api
VITE_WS_URL=wss://chat.armabattles.com/ws
VITE_APP_TITLE=Arma Battles Chat
VITE_APP_NAME=Arma Battles
VITE_PRIMARY_COLOR=#4A90E2
VITE_SECONDARY_COLOR=#2C5F2D
VITE_OAUTH_ENABLED=true
VITE_OAUTH_PROVIDER_NAME=Arma Battles
VITE_OAUTH_AUTHORIZE_URL=https://armabattles.com/oauth/authorize
VITE_OAUTH_CLIENT_ID=019c5d06-b3f3-709a-a212-b4441d609080
VITE_MAIN_SITE_URL=https://armabattles.com
```

---

## 📜 Available Scripts

| Command                                    | Description                                       |
| ------------------------------------------ | ------------------------------------------------- |
| `yarn dev`                                 | Start development server (port 3000)              |
| `yarn build`                               | Build for production                              |
| `yarn build:highmem`                       | Build with increased memory (4GB)                 |
| `yarn build:deps`                          | Build external dependencies (UI components, API client) |
| `yarn preview`                             | Preview production build locally                  |
| `yarn lint`                                | Run ESLint                                        |
| `yarn fmt`                                 | Format code with Prettier                         |
| `yarn typecheck`                           | Run TypeScript type checking                      |
| `yarn start`                               | Serve built files with sirv                       |
| `yarn pull`                                | Setup required assets                             |

---

## 🏗️ Building for Production

### Local Build

```bash
# Install dependencies
yarn

# Build dependencies
yarn build:deps

# Build production bundle
yarn build

# Preview the build
yarn preview
```

The built files will be in the `dist/` directory.

### Build with Docker (Recommended for Windows)

See [BUILD.md](BUILD.md) for comprehensive Docker build instructions.

```bash
# Build Docker image
docker build -t armabattles/chat-frontend:latest .

# Run the container
docker run -p 80:80 armabattles/chat-frontend:latest
```

---

## 🐳 Docker Deployment

### Building the Image

```bash
# Build for production
docker build -t armabattles/chat-frontend:latest .

# Tag for registry
docker tag armabattles/chat-frontend:latest <your-dockerhub>/chat-frontend:latest

# Push to Docker Hub
docker push <your-dockerhub>/chat-frontend:latest
```

### Running with Docker

```bash
# Run on port 80
docker run -d -p 80:80 armabattles/chat-frontend:latest

# Run with custom config
docker run -d -p 80:80 \
  -e VITE_API_URL=https://chat.armabattles.com/api \
  armabattles/chat-frontend:latest
```

---

## 📝 Deployment to VPS

### Using Nginx

1. **Build the project:**
   ```bash
   yarn build
   ```

2. **Copy files to server:**
   ```bash
   scp -r dist/* user@your-server:/var/www/chat.armabattles.com/
   ```

3. **Nginx configuration:**
   ```nginx
   server {
       listen 80;
       server_name chat.armabattles.com;

       root /var/www/chat.armabattles.com;
       index index.html;

       location / {
           try_files $uri $uri/ /index.html;
       }

       location /api {
           proxy_pass http://localhost:14702;
           proxy_http_version 1.1;
           proxy_set_header Upgrade $http_upgrade;
           proxy_set_header Connection "upgrade";
       }

       location /ws {
           proxy_pass http://localhost:14703;
           proxy_http_version 1.1;
           proxy_set_header Upgrade $http_upgrade;
           proxy_set_header Connection "upgrade";
       }
   }
   ```

4. **Setup SSL with Let's Encrypt:**
   ```bash
   sudo certbot --nginx -d chat.armabattles.com
   ```

---

## 🔍 Submodules

This project uses Git submodules for external dependencies:

- `external/components` - UI component library
- `external/revolt.js` - API client library

**Important:** Always clone with `--recursive` flag:

```bash
git clone --recursive <repo-url>
```

If you already cloned without `--recursive`:

```bash
git submodule init
git submodule update
```

After pulling updates:

```bash
git submodule update
```

---

## 🧪 Development Tips

### Memory Issues

If you encounter memory issues during build:

```bash
# Use the high-memory build script
yarn build:highmem

# Or manually set Node memory limit
NODE_OPTIONS='--max-old-space-size=4096' yarn build
```

### Hot Reload

The development server supports hot module replacement (HMR). Changes to source files will automatically reload.

### API Connection

During development, ensure the backend is running on:
- **API:** http://local.revolt.chat:14702
- **WebSocket:** ws://local.revolt.chat:14703

### Testing OAuth Flow

1. Start backend with OAuth configured
2. Start frontend dev server
3. Click "Login with Arma Battles"
4. Should redirect to armabattles.com OAuth page
5. After approval, redirects back to chat

---

## 📚 Project Structure

```
arma-frontend/
├── src/
│   ├── assets/          # Static assets
│   ├── components/      # React components
│   ├── context/         # React contexts
│   ├── lib/             # Utility libraries
│   ├── pages/           # Page components
│   ├── styles/          # Global styles
│   └── main.tsx         # Entry point
├── external/
│   ├── components/      # UI components (submodule)
│   └── revolt.js/       # API client (submodule)
├── public/              # Public static files
├── docker/              # Docker configuration
├── .env.production      # Production environment
├── vite.config.ts       # Vite configuration
└── package.json         # Dependencies
```

---

## 🛠️ Troubleshooting

### Build Fails with Memory Error

```bash
# Increase Node.js heap size
NODE_OPTIONS='--max-old-space-size=4096' yarn build

# Or use Docker Desktop on Windows
docker build -t chat-frontend .
```

### Submodule Errors

```bash
# Re-initialize submodules
git submodule deinit -f .
git submodule init
git submodule update
```

### Port Already in Use

```bash
# Change port in package.json or use:
PORT=3001 yarn dev
```

### API Connection Refused

- Ensure backend is running
- Check `VITE_API_URL` in `.env.local`
- Verify CORS settings on backend

---

## 📖 Further Documentation

For comprehensive build guides and deployment instructions, see [BUILD.md](BUILD.md).

---

## 📝 License

This project is licensed under the **GNU Affero General Public License v3.0** (AGPL-3.0).

---

<div align="center">
Built with ❤️ for the Arma Battles community
</div>
