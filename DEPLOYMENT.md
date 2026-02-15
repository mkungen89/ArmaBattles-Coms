# 🚀 Arma Battles Chat - Production Deployment Guide

Complete guide for deploying Arma Battles Chat to a VPS using Docker.

## 📋 Prerequisites

### Server Requirements
- **OS:** Ubuntu 22.04 LTS or newer (recommended)
- **RAM:** Minimum 4GB, recommended 8GB+
- **Storage:** Minimum 50GB SSD
- **CPU:** 2+ cores recommended
- **Network:** Public IP address and domain name

### Software Requirements
- Docker Engine 24.0+
- Docker Compose 2.0+
- Git
- SSL certificate (Let's Encrypt recommended)

---

## 🛠️ Step 1: Prepare Your VPS

### 1.1 Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Install Docker
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add your user to docker group
sudo usermod -aG docker $USER

# Install Docker Compose
sudo apt install docker-compose-plugin -y

# Verify installation
docker --version
docker compose version
```

### 1.3 Configure Firewall
```bash
# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

---

## 🌐 Step 2: Configure DNS

Point your domain to your VPS IP address:

```
A Record:  chat.armabattles.com  →  YOUR_VPS_IP
A Record:  minio.armabattles.com →  YOUR_VPS_IP  (optional)
```

Wait for DNS propagation (can take up to 48 hours, usually much faster).

---

## 📦 Step 3: Clone Repository

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/ArmaBattles-Coms.git
cd ArmaBattles-Coms

# Or if already cloned on VPS, pull latest
git pull origin main
```

---

## 🔐 Step 4: Configure Environment

### 4.1 Create Environment File
```bash
cp .env.prod.example .env.prod
nano .env.prod
```

### 4.2 Fill in Required Values

**MinIO Credentials:**
```env
MINIO_ROOT_USER=your-secure-username
MINIO_ROOT_PASSWORD=your-very-strong-password-here
```

**RabbitMQ Credentials:**
```env
RABBITMQ_USER=your-rabbitmq-user
RABBITMQ_PASS=your-strong-rabbitmq-password
```

**VAPID Keys (for push notifications):**
```bash
# Generate VAPID keys
npx web-push generate-vapid-keys

# Copy output to .env.prod
VAPID_PUBLIC_KEY=<public-key-from-above>
VAPID_PRIVATE_KEY=<private-key-from-above>
```

**OAuth Configuration:**
```env
OAUTH_CLIENT_ID=019c5d06-b3f3-709a-a212-b4441d609080
OAUTH_CLIENT_SECRET=<get-from-armabattles.com-admin>
```

**Tenor API (optional, for GIFs):**
```env
TENOR_API_KEY=<get-from-https://developers.google.com/tenor>
```

---

## 🔒 Step 5: Setup SSL Certificates

### Option A: Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install certbot -y

# Get SSL certificate
sudo certbot certonly --standalone -d chat.armabattles.com

# Copy certificates to nginx folder
sudo mkdir -p nginx/ssl
sudo cp /etc/letsencrypt/live/chat.armabattles.com/fullchain.pem nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/chat.armabattles.com/privkey.pem nginx/ssl/key.pem
sudo chmod 644 nginx/ssl/*.pem
```

### Option B: Self-Signed (Development Only)

```bash
mkdir -p nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout nginx/ssl/key.pem \
  -out nginx/ssl/cert.pem \
  -subj "/CN=chat.armabattles.com"
```

### Setup Auto-Renewal (Let's Encrypt)

```bash
# Create renewal hook
sudo nano /etc/letsencrypt/renewal-hooks/deploy/copy-certs.sh
```

Add this content:
```bash
#!/bin/bash
cp /etc/letsencrypt/live/chat.armabattles.com/fullchain.pem /path/to/ArmaBattles-Coms/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/chat.armabattles.com/privkey.pem /path/to/ArmaBattles-Coms/nginx/ssl/key.pem
docker exec armabattles-nginx nginx -s reload
```

Make it executable:
```bash
sudo chmod +x /etc/letsencrypt/renewal-hooks/deploy/copy-certs.sh
```

---

## 🏗️ Step 6: Build and Start Services

### 6.1 Build All Services
```bash
# Load environment variables
export $(cat .env.prod | xargs)

# Build all services (this will take 10-30 minutes)
docker compose -f docker-compose.prod.yml build
```

### 6.2 Start Services
```bash
# Start all services
docker compose -f docker-compose.prod.yml up -d

# Check status
docker compose -f docker-compose.prod.yml ps
```

### 6.3 View Logs
```bash
# All services
docker compose -f docker-compose.prod.yml logs -f

# Specific service
docker compose -f docker-compose.prod.yml logs -f delta
docker compose -f docker-compose.prod.yml logs -f frontend
docker compose -f docker-compose.prod.yml logs -f nginx
```

---

## ✅ Step 7: Verify Deployment

### 7.1 Check Service Health
```bash
# Check all containers are running
docker compose -f docker-compose.prod.yml ps

# Expected output: All services should show "Up" status
```

### 7.2 Test Endpoints
```bash
# Test frontend
curl -I https://chat.armabattles.com

# Test API
curl https://chat.armabattles.com/api/

# Test WebSocket (should show upgrade)
curl -i -N -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  https://chat.armabattles.com/ws
```

### 7.3 Access Your Chat
Open your browser and navigate to:
```
https://chat.armabattles.com
```

---

## 🔧 Step 8: Post-Deployment Configuration

### 8.1 Create First Admin User
```bash
# Access MongoDB
docker exec -it armabattles-mongodb mongosh revolt

# Find your user ID after registering
db.users.find({}, {username: 1, _id: 1})

# Make user admin (replace USER_ID)
db.users.updateOne(
  {_id: "USER_ID"},
  {$set: {privileged: true}}
)
```

### 8.2 Configure OAuth on armabattles.com
1. Log into armabattles.com admin panel
2. Go to OAuth clients
3. Update client `019c5d06-b3f3-709a-a212-b4441d609080`:
   - Redirect URI: `https://chat.armabattles.com/auth/callback`
   - Scopes: `profile, email`

---

## 📊 Monitoring & Maintenance

### View Service Logs
```bash
# Real-time logs
docker compose -f docker-compose.prod.yml logs -f

# Last 100 lines
docker compose -f docker-compose.prod.yml logs --tail=100
```

### Restart Services
```bash
# Restart all services
docker compose -f docker-compose.prod.yml restart

# Restart specific service
docker compose -f docker-compose.prod.yml restart delta
```

### Update Deployment
```bash
# Pull latest code
git pull origin main

# Rebuild and restart
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
```

### Backup Data
```bash
# Create backup directory
mkdir -p backups

# Backup MongoDB
docker exec armabattles-mongodb mongodump \
  --out=/tmp/backup
docker cp armabattles-mongodb:/tmp/backup \
  ./backups/mongodb-$(date +%Y%m%d)

# Backup MinIO data
docker run --rm \
  -v armabattles_minio_data:/data \
  -v $(pwd)/backups:/backup \
  alpine tar czf /backup/minio-$(date +%Y%m%d).tar.gz /data
```

---

## 🐛 Troubleshooting

### Services Won't Start
```bash
# Check logs for errors
docker compose -f docker-compose.prod.yml logs

# Check disk space
df -h

# Check memory
free -h
```

### Can't Connect to Chat
```bash
# Verify Nginx is running
docker compose -f docker-compose.prod.yml ps nginx

# Check Nginx logs
docker compose -f docker-compose.prod.yml logs nginx

# Test internal connectivity
docker exec armabattles-nginx curl http://frontend:5000
```

### Database Issues
```bash
# Access MongoDB shell
docker exec -it armabattles-mongodb mongosh revolt

# Check collections
show collections

# Check user count
db.users.countDocuments()
```

### Out of Memory
```bash
# Check container memory usage
docker stats

# Restart services to free memory
docker compose -f docker-compose.prod.yml restart
```

---

## 🔄 Updating

### Update Application
```bash
cd ArmaBattles-Coms
git pull origin main
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
```

### Update Dependencies
```bash
# Update Docker images
docker compose -f docker-compose.prod.yml pull

# Restart services
docker compose -f docker-compose.prod.yml up -d
```

---

## 🛡️ Security Best Practices

1. **Keep system updated:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Use strong passwords** for all services (MinIO, RabbitMQ, etc.)

3. **Restrict MongoDB access** - only accessible from localhost/internal network

4. **Enable firewall** - only open necessary ports (80, 443, 22)

5. **Regular backups** - backup MongoDB and MinIO data regularly

6. **Monitor logs** - check for suspicious activity

7. **SSL certificates** - keep Let's Encrypt certificates auto-renewed

---

## 📞 Support

If you encounter issues:

1. Check logs: `docker compose -f docker-compose.prod.yml logs`
2. Review this guide
3. Check TODO.md for known issues
4. Open an issue on GitHub

---

## 🎉 Success!

Your Arma Battles Chat is now running at:
- **Chat:** https://chat.armabattles.com
- **MinIO Console:** https://minio.armabattles.com (if configured)

Happy chatting! 🚀
