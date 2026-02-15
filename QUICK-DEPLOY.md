# ⚡ Quick Manual Deployment Guide

Följ dessa steg exakt för att deploya Arma Battles Chat till VPS.

## 📋 Pre-Deployment Checklist

Innan du börjar, se till att du har:

- [ ] VPS med Ubuntu 22.04 LTS (8GB+ RAM)
- [ ] SSH access till VPS
- [ ] Domain pekar till VPS IP: `chat.armabattles.com → VPS_IP`
- [ ] OAuth endpoints klara på armabattles.com (Laravel)
- [ ] VAPID keys genererade
- [ ] `.env.prod` fylld i med alla secrets

## 🚀 Deployment Steps

### 1. Update VPS och installera Docker (10 min)

```bash
# SSH till din VPS
ssh root@your-vps-ip

# Update system
sudo apt update && sudo apt upgrade -y

# Installera Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Lägg till user i docker group
sudo usermod -aG docker $USER

# Installera Docker Compose
sudo apt install docker-compose-plugin -y

# Verifiera installation
docker --version
docker compose version

# Logout och login igen för group changes
exit
ssh root@your-vps-ip
```

### 2. Konfigurera Firewall (2 min)

```bash
# Allow SSH (viktigt!)
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

### 3. Klona Repository (5 min)

```bash
# Skapa directory
mkdir -p /opt/armabattles-chat
cd /opt/armabattles-chat

# Klona repo
git clone https://github.com/YOUR-USERNAME/YOUR-REPO.git .

# Eller om privat repo:
git clone https://YOUR-TOKEN@github.com/YOUR-USERNAME/YOUR-REPO.git .
```

### 4. Kopiera Environment File (2 min)

```bash
# Från din lokala dator (Git Bash):
scp C:/revolt/.env.prod root@your-vps-ip:/opt/armabattles-chat/.env.prod

# Eller skapa manuellt på VPS:
nano .env.prod
# Klistra in innehållet från din lokala .env.prod
# Ctrl+O för att spara, Ctrl+X för att avsluta
```

### 5. Setup SSL Certificates (5 min)

```bash
# På VPS:

# Installera Certbot
sudo apt install certbot -y

# Stoppa eventuella services på port 80/443
docker compose -f docker-compose.prod.yml down 2>/dev/null || true

# Generera SSL certificate
sudo certbot certonly --standalone -d chat.armabattles.com

# Kopiera till nginx directory
sudo mkdir -p nginx/ssl
sudo cp /etc/letsencrypt/live/chat.armabattles.com/fullchain.pem nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/chat.armabattles.com/privkey.pem nginx/ssl/key.pem
sudo chmod 644 nginx/ssl/*.pem
```

### 6. Deploy! (20-30 min första gången)

```bash
# Kör deployment script
chmod +x deploy.sh
./deploy.sh
```

Scriptet kommer att:
1. ✅ Validera .env.prod finns
2. ✅ Validera SSL certificates
3. ✅ Bygga alla Docker images (tar 20-30 min)
4. ✅ Starta alla services
5. ✅ Visa status

### 7. Verifiera Deployment (2 min)

```bash
# Kolla status
docker compose -f docker-compose.prod.yml ps

# Alla services ska visa "Up" och "healthy"
```

**Förväntad output:**
```
NAME                    STATUS         PORTS
armabattles-mongodb     Up (healthy)   27017/tcp
armabattles-redis       Up (healthy)   6379/tcp
armabattles-minio       Up (healthy)   9000/tcp, 9001/tcp
armabattles-rabbitmq    Up (healthy)   5672/tcp, 15672/tcp
armabattles-delta       Up (healthy)   14702/tcp
armabattles-bonfire     Up             14703/tcp
armabattles-autumn      Up (healthy)   14704/tcp
armabattles-january     Up (healthy)   14705/tcp
armabattles-gifbox      Up (healthy)   14706/tcp
armabattles-frontend    Up (healthy)   5000/tcp
armabattles-nginx       Up (healthy)   0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp
```

### 8. Test Application (5 min)

```bash
# Test API
curl -I https://chat.armabattles.com/api/

# Expected: HTTP/2 200

# Test frontend
curl -I https://chat.armabattles.com/

# Expected: HTTP/2 200
```

**I din browser:**
1. Gå till `https://chat.armabattles.com`
2. Klicka "Login with Arma Battles"
3. Ska redirecta till `armabattles.com/oauth/authorize`
4. Efter godkännande → tillbaka till chatten

### 9. Setup Auto-Renewal för SSL (5 min)

```bash
# Skapa renewal hook
sudo mkdir -p /etc/letsencrypt/renewal-hooks/deploy
sudo nano /etc/letsencrypt/renewal-hooks/deploy/copy-certs.sh
```

Lägg in:
```bash
#!/bin/bash
cp /etc/letsencrypt/live/chat.armabattles.com/fullchain.pem /opt/armabattles-chat/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/chat.armabattles.com/privkey.pem /opt/armabattles-chat/nginx/ssl/key.pem
docker exec armabattles-nginx nginx -s reload
```

Gör executable:
```bash
sudo chmod +x /etc/letsencrypt/renewal-hooks/deploy/copy-certs.sh

# Test renewal (dry-run)
sudo certbot renew --dry-run
```

## ✅ Post-Deployment

### Create First Admin User

```bash
# Access MongoDB
docker exec -it armabattles-mongodb mongosh revolt

# Registrera en användare först via UI, sedan:

# Hitta din user ID
db.users.find({}, {username: 1, _id: 1})

# Gör dig till admin (byt USER_ID)
db.users.updateOne(
  {_id: "USER_ID"},
  {$set: {privileged: true}}
)

# Avsluta mongo
exit
```

## 🔧 Useful Commands

### View Logs
```bash
# Alla services
docker compose -f docker-compose.prod.yml logs -f

# Specifik service
docker compose -f docker-compose.prod.yml logs -f delta
docker compose -f docker-compose.prod.yml logs -f nginx
```

### Restart Services
```bash
# Alla
docker compose -f docker-compose.prod.yml restart

# En specifik
docker compose -f docker-compose.prod.yml restart delta
```

### Update Deployment
```bash
cd /opt/armabattles-chat
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build
```

### Stop Everything
```bash
docker compose -f docker-compose.prod.yml down
```

### Check Resource Usage
```bash
docker stats
```

## 🐛 Troubleshooting

### 502 Bad Gateway
```bash
# Check backend services
docker compose -f docker-compose.prod.yml logs delta
docker compose -f docker-compose.prod.yml logs bonfire

# Restart backend
docker compose -f docker-compose.prod.yml restart delta bonfire
```

### SSL Certificate Error
```bash
# Check certificate
openssl x509 -in nginx/ssl/cert.pem -text -noout

# Renew manually
sudo certbot renew --force-renewal
```

### Out of Memory
```bash
# Check memory
free -h

# If out of memory, restart services
docker compose -f docker-compose.prod.yml restart
```

### Can't Connect to Chat
```bash
# Check Nginx
docker compose -f docker-compose.prod.yml logs nginx

# Check DNS
nslookup chat.armabattles.com

# Check firewall
sudo ufw status
```

## 📊 Monitoring

### Check Service Health
```bash
# All services status
docker compose -f docker-compose.prod.yml ps

# Container resource usage
docker stats --no-stream
```

### Database Backup (Recommended Daily)
```bash
# Create backup directory
mkdir -p /opt/backups

# Backup MongoDB
docker exec armabattles-mongodb mongodump --out=/tmp/backup
docker cp armabattles-mongodb:/tmp/backup /opt/backups/mongodb-$(date +%Y%m%d)

# Backup MinIO
docker run --rm \
  -v armabattles_minio_data:/data \
  -v /opt/backups:/backup \
  alpine tar czf /backup/minio-$(date +%Y%m%d).tar.gz /data
```

## 🎉 Success!

Din Arma Battles Chat körs nu på:
- **Chat:** https://chat.armabattles.com
- **API:** https://chat.armabattles.com/api
- **WebSocket:** wss://chat.armabattles.com/ws

Next steps:
1. Test all features
2. Create first admin user
3. Invite users to test
4. Monitor logs for errors
5. Setup automated backups
6. Consider migrating to CI/CD later

---

**Behöver hjälp?** Kolla `DEPLOYMENT.md` för mer detaljer eller fråga!
