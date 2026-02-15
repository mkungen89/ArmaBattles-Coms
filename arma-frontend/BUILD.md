# Arma Battles Chat Frontend - Build Guide

Comprehensive guide for building and deploying the Arma Battles Chat frontend.

---

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Local Development](#local-development)
- [Production Build](#production-build)
- [Docker Build](#docker-build-recommended)
- [Building on Windows](#building-on-windows)
- [Deployment](#deployment)
- [Optimization](#optimization)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### System Requirements

- **Node.js:** v20.x or higher
- **Yarn:** v3.x (Berry) - managed via Corepack
- **RAM:** Minimum 4GB for building (8GB recommended)
- **Storage:** ~2GB for dependencies and build artifacts

### Software Installation

#### Install Node.js

**Windows:**
```bash
# Download from nodejs.org or use winget:
winget install OpenJS.NodeJS.LTS
```

**Linux (Ubuntu/Debian):**
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

**macOS:**
```bash
brew install node@20
```

Verify installation:
```bash
node --version  # Should be v20.x or higher
```

#### Enable Yarn (Corepack)

Yarn 3 is managed via Node.js Corepack:

```bash
corepack enable
corepack prepare yarn@stable --activate
yarn --version  # Should be 3.x or higher
```

---

## Local Development

### Clone the Repository

```bash
# Clone with submodules (IMPORTANT!)
git clone --recursive <your-repo-url> arma-frontend
cd arma-frontend

# If already cloned without --recursive:
git submodule init
git submodule update
```

### Install Dependencies

```bash
# Install all dependencies
yarn install
```

This will:
- Install all npm packages
- Set up Yarn Berry (v3)
- Prepare workspace dependencies

### Build External Dependencies

**IMPORTANT:** You must build external dependencies before the first run:

```bash
yarn build:deps
```

This builds:
- `external/components` - UI components library
- `external/revolt.js` - API client library

### Start Development Server

```bash
yarn dev
```

The development server will start at:
- **URL:** http://local.revolt.chat:3000
- **Note:** Uses `local.revolt.chat` domain (resolves to 127.0.0.1)

### Development Environment Variables

Create `.env.local` for development:

```env
VITE_API_URL=http://local.revolt.chat:14702/api
VITE_WS_URL=ws://local.revolt.chat:14703
VITE_APP_TITLE=Arma Battles Chat (Dev)
VITE_APP_NAME=Arma Battles
```

### Hot Module Replacement

The dev server supports HMR (Hot Module Replacement). Changes to source files will automatically reload the page.

---

## Production Build

### Standard Build

```bash
# Full production build
yarn build
```

This will:
1. Pull required assets
2. Build external dependencies
3. Build the main application
4. Output to `dist/` directory

### High Memory Build

If you encounter memory issues:

```bash
# Build with 4GB memory limit
yarn build:highmem

# Or manually set memory:
NODE_OPTIONS='--max-old-space-size=4096' yarn build
```

### Build Output

After building, you'll find:
- `dist/` - Production-ready static files
- `dist/index.html` - Entry point
- `dist/assets/` - JavaScript, CSS, and other assets

### Preview Production Build

```bash
# Preview locally
yarn preview

# Or use sirv
yarn start
```

Access at http://localhost:4173

---

## Docker Build (Recommended)

Docker is the recommended build method, especially on Windows, as it provides:
- Consistent build environment
- Isolated dependencies
- Easy deployment

### Prerequisites

Install Docker Desktop:
- **Windows:** [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop/)
- **Linux:** `sudo apt install docker.io docker-compose`
- **macOS:** [Docker Desktop for Mac](https://www.docker.com/products/docker-desktop/)

### Build Docker Image

```bash
# Build the image
docker build -t armabattles/chat-frontend:latest .

# Check image size
docker images | grep chat-frontend
```

### Run Docker Container

```bash
# Run on port 80
docker run -d -p 80:80 --name arma-chat armabattles/chat-frontend:latest

# Run on custom port
docker run -d -p 8080:80 --name arma-chat armabattles/chat-frontend:latest

# Access at http://localhost or http://localhost:8080
```

### Docker Build with Custom Environment

```bash
# Build with build arguments
docker build \
  --build-arg VITE_API_URL=https://chat.armabattles.com/api \
  --build-arg VITE_WS_URL=wss://chat.armabattles.com/ws \
  -t armabattles/chat-frontend:latest .
```

### Multi-Stage Docker Build

The included Dockerfile uses a multi-stage build:

1. **Stage 1 (builder):** Builds the application
2. **Stage 2 (production):** Serves static files with Nginx

This results in a small, optimized production image (~50MB vs ~1GB).

---

## Building on Windows

### Method 1: Docker Desktop (Recommended)

Docker Desktop provides the most reliable build environment on Windows.

1. **Install Docker Desktop:**
   - Download from [docker.com](https://www.docker.com/products/docker-desktop/)
   - Enable WSL 2 backend during installation

2. **Build with Docker:**
   ```bash
   docker build -t armabattles/chat-frontend:latest .
   ```

3. **Run locally:**
   ```bash
   docker run -p 80:80 armabattles/chat-frontend:latest
   ```

### Method 2: WSL 2 (Windows Subsystem for Linux)

1. **Install WSL 2:**
   ```powershell
   wsl --install
   ```

2. **Install Ubuntu from Microsoft Store**

3. **Inside WSL:**
   ```bash
   # Install Node.js
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt-get install -y nodejs

   # Enable Yarn
   corepack enable

   # Clone and build
   git clone --recursive <repo-url>
   cd arma-frontend
   yarn
   yarn build:deps
   yarn build:highmem
   ```

### Method 3: Native Windows Build

```powershell
# Install Node.js 20
winget install OpenJS.NodeJS.LTS

# Clone repo
git clone --recursive <repo-url>
cd arma-frontend

# Enable Yarn
corepack enable

# Install and build
yarn install
yarn build:deps

# Build with increased memory
$env:NODE_OPTIONS="--max-old-space-size=4096"
yarn build
```

### Memory Considerations on Windows

Windows builds may require more memory. If you encounter errors:

1. **Increase Node.js heap:**
   ```powershell
   $env:NODE_OPTIONS="--max-old-space-size=6144"
   yarn build
   ```

2. **Close unnecessary programs** to free RAM

3. **Use Docker Desktop** for consistent builds

---

## Deployment

### Deploy to VPS (Ubuntu/Debian)

#### Option 1: Deploy Built Files

```bash
# On your local machine:
yarn build

# Copy to server:
scp -r dist/* user@your-server:/var/www/chat.armabattles.com/

# On server: Configure Nginx (see below)
```

#### Option 2: Build on Server

```bash
# SSH to server
ssh user@your-server

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Enable Yarn
corepack enable

# Clone and build
cd /var/www
sudo git clone --recursive <repo-url> chat.armabattles.com
sudo chown -R $USER:$USER chat.armabattles.com
cd chat.armabattles.com
yarn
yarn build:deps
yarn build:highmem
```

### Nginx Configuration

Create `/etc/nginx/sites-available/chat.armabattles.com`:

```nginx
server {
    listen 80;
    server_name chat.armabattles.com;
    root /var/www/chat.armabattles.com/dist;
    index index.html;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # SPA fallback
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/chat.armabattles.com /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d chat.armabattles.com

# Auto-renewal is set up automatically
sudo certbot renew --dry-run
```

### Deploy with Docker

```bash
# On server:
docker pull armabattles/chat-frontend:latest
docker stop arma-chat || true
docker rm arma-chat || true
docker run -d -p 80:80 --name arma-chat --restart unless-stopped \
  armabattles/chat-frontend:latest
```

---

## Optimization

### Build Optimization Tips

1. **Use Production Mode:**
   ```bash
   NODE_ENV=production yarn build
   ```

2. **Enable Compression:**
   - Nginx gzip (see Nginx config above)
   - Brotli compression (optional)

3. **Analyze Bundle Size:**
   ```bash
   yarn build
   # Check dist/assets/ for file sizes
   ```

4. **Code Splitting:**
   - Vite automatically code-splits routes
   - Lazy load heavy components

### Performance Checklist

- [ ] Enable gzip/brotli compression
- [ ] Set proper cache headers
- [ ] Use CDN for static assets (optional)
- [ ] Minify HTML, CSS, JS (Vite does this automatically)
- [ ] Optimize images (use WebP)
- [ ] Enable HTTP/2 on Nginx
- [ ] Use service worker for offline support (PWA)

---

## Troubleshooting

### Build Errors

#### "JavaScript heap out of memory"

**Solution:**
```bash
# Increase Node.js memory limit
NODE_OPTIONS='--max-old-space-size=4096' yarn build

# Or use the high-memory script
yarn build:highmem

# Or use Docker
docker build -t chat-frontend .
```

#### "Cannot find module 'external/components'"

**Solution:**
```bash
# Rebuild external dependencies
git submodule update --init --recursive
yarn build:deps
```

#### "ENOENT: no such file or directory"

**Solution:**
```bash
# Pull required assets
yarn pull

# Or run full build
yarn build
```

### Submodule Issues

#### Submodules not initialized

```bash
git submodule init
git submodule update
```

#### Submodule build fails

```bash
# Clean and rebuild
cd external/components
yarn install
yarn build:esm

cd ../revolt.js
yarn install
yarn build

cd ../..
```

### Docker Issues

#### Docker build fails on Windows

**Solution:**
- Enable WSL 2 backend in Docker Desktop
- Allocate more RAM to Docker (Settings → Resources)
- Use `--memory=4g` flag:
  ```bash
  docker build --memory=4g -t chat-frontend .
  ```

#### Permission denied (Linux)

**Solution:**
```bash
sudo usermod -aG docker $USER
newgrp docker
```

### Runtime Issues

#### API connection refused

**Check:**
- Is backend running?
- Is `VITE_API_URL` correct in `.env.production`?
- Check browser console for errors

#### OAuth redirect fails

**Check:**
- Redirect URI matches exactly in OAuth config
- HTTPS is enabled on production
- CORS is configured on backend

#### WebSocket connection fails

**Check:**
- Nginx proxy configuration for `/ws`
- `proxy_read_timeout` is set high enough
- Firewall allows WebSocket connections

---

## CI/CD Pipeline (Optional)

### GitHub Actions Example

Create `.github/workflows/build.yml`:

```yaml
name: Build and Deploy

on:
  push:
    branches: [main]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
        with:
          submodules: recursive

      - uses: actions/setup-node@v3
        with:
          node-version: 20

      - name: Enable Corepack
        run: corepack enable

      - name: Install dependencies
        run: yarn install

      - name: Build dependencies
        run: yarn build:deps

      - name: Build production
        run: yarn build:highmem

      - name: Deploy to VPS
        uses: appleboy/scp-action@master
        with:
          host: ${{ secrets.VPS_HOST }}
          username: ${{ secrets.VPS_USER }}
          key: ${{ secrets.VPS_SSH_KEY }}
          source: "dist/*"
          target: "/var/www/chat.armabattles.com/"
```

---

## Build Checklist

Before deploying to production:

- [ ] `.env.production` configured correctly
- [ ] API and WebSocket URLs point to production
- [ ] OAuth client ID and redirect URI are correct
- [ ] External dependencies built (`yarn build:deps`)
- [ ] Production build successful (`yarn build`)
- [ ] Build tested locally (`yarn preview`)
- [ ] Gzip compression enabled on server
- [ ] SSL certificate installed
- [ ] Cache headers configured
- [ ] Error tracking configured (Sentry, etc.)
- [ ] Analytics configured (optional)

---

## Additional Resources

- [Vite Documentation](https://vitejs.dev/)
- [Preact Documentation](https://preactjs.com/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Docker Documentation](https://docs.docker.com/)
- [Let's Encrypt](https://letsencrypt.org/)

---

**Need help?** Contact the Arma Battles development team.
