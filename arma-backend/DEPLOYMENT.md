# Arma Battles Chat Backend - Deployment Guide

Comprehensive guide for deploying the Arma Battles Chat backend to production.

---

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Deployment Options](#deployment-options)
  - [Docker Deployment (Recommended)](#docker-deployment-recommended)
  - [Manual Deployment](#manual-deployment)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Nginx Configuration](#nginx-configuration)
- [Systemd Services](#systemd-services)
- [OAuth Setup](#oauth-setup)
- [SSL/TLS Certificates](#ssltls-certificates)
- [Monitoring](#monitoring)
- [Backup Strategy](#backup-strategy)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Server Requirements

- **OS:** Ubuntu 22.04 LTS or newer (or similar Linux distribution)
- **RAM:** Minimum 2GB, recommended 4GB+
- **CPU:** 2+ cores recommended
- **Storage:** 20GB+ available space
- **Network:** Static IP or domain name configured

### Software Requirements

- **Docker & Docker Compose** (for Docker deployment)
- **Rust 1.86.0+** (for manual deployment)
- **MongoDB 5.0+**
- **Redis 6.0+**
- **Nginx** (reverse proxy)
- **SSL Certificate** (Let's Encrypt recommended)

---

## Deployment Options

### Docker Deployment (Recommended)

Docker deployment is the easiest and most reliable method.

#### Step 1: Install Docker

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add your user to docker group
sudo usermod -aG docker $USER
newgrp docker

# Verify installation
docker --version
docker compose version
```

#### Step 2: Clone Repository

```bash
cd /opt
sudo git clone <your-repo-url> arma-chat-backend
sudo chown -R $USER:$USER arma-chat-backend
cd arma-chat-backend
```

#### Step 3: Configure Environment

```bash
# Copy production config
cp Revolt.production.toml Revolt.toml

# Edit configuration
nano Revolt.toml
```

Set the following environment variables:

```bash
# Create .env file
cat > .env << 'EOF'
# OAuth
OAUTH_CLIENT_SECRET=your-oauth-client-secret

# S3/MinIO
S3_ACCESS_KEY=your-minio-access-key
S3_SECRET_KEY=your-minio-secret-key

# MongoDB
MONGO_INITDB_ROOT_USERNAME=admin
MONGO_INITDB_ROOT_PASSWORD=your-strong-password

# Redis
REDIS_PASSWORD=your-redis-password

# Email (optional)
SMTP_PASSWORD=your-smtp-password

# Sentry (optional)
SENTRY_DSN=your-sentry-dsn
EOF

chmod 600 .env
```

#### Step 4: Build & Run

```bash
# Build the Docker image
docker compose build

# Start all services
docker compose up -d

# Check logs
docker compose logs -f

# Check status
docker compose ps
```

#### Step 5: Verify Deployment

```bash
# Test API endpoint
curl http://localhost:14702/

# Test WebSocket
wscat -c ws://localhost:14703
```

---

### Manual Deployment

For advanced users who want direct control.

#### Step 1: Install Rust

```bash
# Install Rust
curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh
source $HOME/.cargo/env

# Verify
rustc --version
```

#### Step 2: Clone & Build

```bash
cd /opt
sudo git clone <your-repo-url> arma-chat-backend
sudo chown -R $USER:$USER arma-chat-backend
cd arma-chat-backend

# Build in release mode
cargo build --release
```

Binaries will be in `target/release/`:
- `revolt-delta` (API server)
- `revolt-bonfire` (WebSocket server)
- `revolt-autumn` (File server)
- `revolt-january` (Proxy server)
- `revolt-gifbox` (Tenor proxy)

#### Step 3: Install Services

```bash
# Copy binaries to /usr/local/bin
sudo cp target/release/revolt-* /usr/local/bin/

# Create service user
sudo useradd -r -s /bin/false revolt

# Create directories
sudo mkdir -p /etc/revolt /var/lib/revolt /var/log/revolt
sudo chown revolt:revolt /var/lib/revolt /var/log/revolt

# Copy configuration
sudo cp Revolt.production.toml /etc/revolt/Revolt.toml
sudo chown revolt:revolt /etc/revolt/Revolt.toml
sudo chmod 600 /etc/revolt/Revolt.toml
```

See [Systemd Services](#systemd-services) section for service configuration.

---

## Configuration

### Production Configuration File

Edit `Revolt.production.toml`:

```toml
[application]
app_name = "Arma Battles Chat"
app_url = "https://chat.armabattles.com"

[hosts]
app = "https://chat.armabattles.com"
api = "https://chat.armabattles.com/api"
events = "wss://chat.armabattles.com/ws"

[database]
mongodb = "mongodb://admin:password@localhost:27017/armabattles?authSource=admin"
redis = "redis://:password@localhost:6379/"

[api.oauth]
provider_name = "Arma Battles"
client_id = "019c5d06-b3f3-709a-a212-b4441d609080"
authorize_endpoint = "https://armabattles.com/oauth/authorize"
token_endpoint = "https://armabattles.com/oauth/token"
userinfo_endpoint = "https://armabattles.com/oauth/user"
redirect_uri = "https://chat.armabattles.com/auth/callback"
```

### Environment Variables

Sensitive values should be set via environment variables:

```bash
export OAUTH_CLIENT_SECRET="your-secret"
export S3_ACCESS_KEY="your-key"
export S3_SECRET_KEY="your-secret"
export MONGO_PASSWORD="your-password"
export REDIS_PASSWORD="your-password"
```

---

## Database Setup

### MongoDB

```bash
# Install MongoDB
wget -qO - https://www.mongodb.org/static/pgp/server-6.0.asc | sudo apt-key add -
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/6.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-6.0.list
sudo apt update
sudo apt install -y mongodb-org

# Start MongoDB
sudo systemctl start mongod
sudo systemctl enable mongod

# Create database and user
mongosh << 'EOF'
use admin
db.createUser({
  user: "admin",
  pwd: "your-strong-password",
  roles: ["root"]
})

use armabattles
db.createUser({
  user: "chatuser",
  pwd: "your-chat-password",
  roles: ["readWrite"]
})
EOF
```

### Redis

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
# Set: requirepass your-redis-password

# Restart Redis
sudo systemctl restart redis
sudo systemctl enable redis
```

### MinIO (S3-compatible storage)

```bash
# Download MinIO
wget https://dl.min.io/server/minio/release/linux-amd64/minio
sudo mv minio /usr/local/bin/
sudo chmod +x /usr/local/bin/minio

# Create data directory
sudo mkdir -p /var/lib/minio
sudo chown $USER:$USER /var/lib/minio

# Run MinIO (or use systemd service)
minio server /var/lib/minio --console-address ":9001"
```

Access MinIO console at `http://your-server:9001` and create a bucket named `armabattles-chat`.

---

## Nginx Configuration

### Install Nginx

```bash
sudo apt install -y nginx
```

### Configuration File

Create `/etc/nginx/sites-available/chat.armabattles.com`:

```nginx
# Upstream servers
upstream api_backend {
    server localhost:14702;
}

upstream ws_backend {
    server localhost:14703;
}

upstream files_backend {
    server localhost:14704;
}

# HTTP -> HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name chat.armabattles.com;

    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name chat.armabattles.com;

    # SSL configuration
    ssl_certificate /etc/letsencrypt/live/chat.armabattles.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/chat.armabattles.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Client body size for file uploads
    client_max_body_size 25M;

    # Root location (serve frontend)
    location / {
        root /var/www/chat.armabattles.com;
        try_files $uri $uri/ /index.html;
    }

    # API endpoints
    location /api {
        proxy_pass http://api_backend;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_buffering off;
    }

    # WebSocket endpoint
    location /ws {
        proxy_pass http://ws_backend;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_buffering off;
        proxy_read_timeout 86400;
    }

    # File uploads/downloads
    location /files {
        proxy_pass http://files_backend;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Enable Configuration

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/chat.armabattles.com /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

---

## Systemd Services

### Create Service Files

#### revolt-delta.service (API)

`/etc/systemd/system/revolt-delta.service`:

```ini
[Unit]
Description=Arma Battles Chat API Server (Delta)
After=network.target mongodb.service redis.service

[Service]
Type=simple
User=revolt
Group=revolt
WorkingDirectory=/opt/arma-chat-backend
ExecStart=/usr/local/bin/revolt-delta
Restart=always
RestartSec=10
Environment="REVOLT_CONFIG=/etc/revolt/Revolt.toml"
Environment="RUST_LOG=info"

[Install]
WantedBy=multi-user.target
```

#### revolt-bonfire.service (WebSocket)

`/etc/systemd/system/revolt-bonfire.service`:

```ini
[Unit]
Description=Arma Battles Chat WebSocket Server (Bonfire)
After=network.target mongodb.service redis.service

[Service]
Type=simple
User=revolt
Group=revolt
WorkingDirectory=/opt/arma-chat-backend
ExecStart=/usr/local/bin/revolt-bonfire
Restart=always
RestartSec=10
Environment="REVOLT_CONFIG=/etc/revolt/Revolt.toml"
Environment="RUST_LOG=info"

[Install]
WantedBy=multi-user.target
```

#### revolt-autumn.service (File Server)

`/etc/systemd/system/revolt-autumn.service`:

```ini
[Unit]
Description=Arma Battles Chat File Server (Autumn)
After=network.target

[Service]
Type=simple
User=revolt
Group=revolt
WorkingDirectory=/opt/arma-chat-backend
ExecStart=/usr/local/bin/revolt-autumn
Restart=always
RestartSec=10
Environment="REVOLT_CONFIG=/etc/revolt/Revolt.toml"
Environment="RUST_LOG=info"

[Install]
WantedBy=multi-user.target
```

### Enable and Start Services

```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable services
sudo systemctl enable revolt-delta revolt-bonfire revolt-autumn

# Start services
sudo systemctl start revolt-delta revolt-bonfire revolt-autumn

# Check status
sudo systemctl status revolt-delta revolt-bonfire revolt-autumn

# View logs
sudo journalctl -u revolt-delta -f
```

---

## OAuth Setup

OAuth integration with the main Arma Battles website is required for user authentication.

### Backend Configuration

Already configured in `Revolt.production.toml`:

```toml
[api.oauth]
provider_name = "Arma Battles"
client_id = "019c5d06-b3f3-709a-a212-b4441d609080"
authorize_endpoint = "https://armabattles.com/oauth/authorize"
token_endpoint = "https://armabattles.com/oauth/token"
userinfo_endpoint = "https://armabattles.com/oauth/user"
redirect_uri = "https://chat.armabattles.com/auth/callback"
```

### Laravel OAuth Server Configuration

On the main `armabattles.com` Laravel application:

1. Ensure OAuth client exists with ID: `019c5d06-b3f3-709a-a212-b4441d609080`
2. Set redirect URI: `https://chat.armabattles.com/auth/callback`
3. Grant scopes: `profile`, `email`

### Testing OAuth Flow

1. Visit `https://chat.armabattles.com`
2. Click "Login with Arma Battles"
3. Redirected to `armabattles.com/oauth/authorize`
4. Approve authorization
5. Redirected back to chat with session

---

## SSL/TLS Certificates

### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d chat.armabattles.com

# Test auto-renewal
sudo certbot renew --dry-run
```

Certificates will be stored in `/etc/letsencrypt/live/chat.armabattles.com/`.

### Auto-renewal

Certbot automatically sets up a cron job or systemd timer for renewal. Verify:

```bash
sudo systemctl status certbot.timer
```

---

## Monitoring

### Logs

```bash
# Docker logs
docker compose logs -f

# Systemd logs
sudo journalctl -u revolt-delta -f
sudo journalctl -u revolt-bonfire -f

# Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

### Health Checks

```bash
# API health
curl https://chat.armabattles.com/api/health

# MongoDB
mongosh --eval "db.adminCommand('ping')"

# Redis
redis-cli ping
```

### Sentry Integration (Optional)

Add Sentry DSN to `Revolt.toml` for error tracking:

```toml
[sentry]
api = "https://your-dsn@sentry.io/project"
events = "https://your-dsn@sentry.io/project"
files = "https://your-dsn@sentry.io/project"
```

---

## Backup Strategy

### MongoDB Backups

```bash
# Create backup script
cat > /usr/local/bin/backup-mongo.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/var/backups/mongodb"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

mongodump --uri="mongodb://admin:password@localhost:27017/armabattles?authSource=admin" \
          --out="$BACKUP_DIR/dump_$DATE"

# Keep only last 7 days
find $BACKUP_DIR -type d -mtime +7 -exec rm -rf {} \;
EOF

chmod +x /usr/local/bin/backup-mongo.sh

# Add to cron (daily at 2 AM)
echo "0 2 * * * /usr/local/bin/backup-mongo.sh" | sudo crontab -
```

### Redis Backups

Redis automatically saves to `/var/lib/redis/dump.rdb`. Back up this file regularly.

### File Storage Backups

Back up MinIO data directory `/var/lib/minio` or use S3 bucket versioning.

---

## Troubleshooting

### Service Won't Start

```bash
# Check logs
sudo journalctl -u revolt-delta -n 50

# Verify config
revolt-delta --check-config

# Check ports
sudo netstat -tulpn | grep revolt
```

### Database Connection Issues

```bash
# Test MongoDB connection
mongosh "mongodb://admin:password@localhost:27017/?authSource=admin"

# Test Redis
redis-cli -a your-password ping
```

### OAuth Errors

- Verify client ID and secret
- Check redirect URI matches exactly
- Ensure OAuth endpoints are reachable
- Check CORS configuration

### File Upload Failures

- Check MinIO is running
- Verify S3 credentials
- Check file size limits in Nginx and app config
- Verify bucket permissions

### High Memory Usage

```bash
# Check service memory
docker stats
# or
ps aux | grep revolt

# Adjust limits if needed
```

### WebSocket Connection Drops

- Check Nginx proxy timeout settings
- Verify firewall allows WebSocket connections
- Check `proxy_read_timeout` in Nginx config

---

## Production Checklist

- [ ] MongoDB authentication enabled
- [ ] Redis password set
- [ ] Strong OAuth client secret
- [ ] SSL/TLS certificates installed
- [ ] Nginx reverse proxy configured
- [ ] Firewall rules configured (ufw/iptables)
- [ ] Automated backups configured
- [ ] Monitoring/logging set up
- [ ] Error tracking (Sentry) configured
- [ ] Rate limiting enabled
- [ ] CORS properly configured
- [ ] Email notifications working
- [ ] OAuth flow tested end-to-end
- [ ] File uploads tested
- [ ] WebSocket connections tested

---

## Additional Resources

- [MongoDB Manual](https://docs.mongodb.com/)
- [Redis Documentation](https://redis.io/documentation)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Docker Documentation](https://docs.docker.com/)

---

**Need help?** Contact the Arma Battles development team.
