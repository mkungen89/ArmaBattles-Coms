# 🚀 Deployment Options

This project supports **three deployment strategies**. Choose the one that fits your workflow.

## 📊 Comparison

| Method | Build Location | Deployment Speed | VPS Requirements | Best For |
|--------|---------------|------------------|------------------|----------|
| **Option 1: Manual** | VPS | Slow (20-30 min) | 8GB+ RAM | First-time setup, testing |
| **Option 2: CI/CD** | GitHub Actions | Fast (5 min deploy) | 4GB RAM | Production, teams |
| **Option 3: Local Build** | Your PC | Medium (10 min) | 4GB RAM | Solo dev, offline |

---

## 🛠️ Option 1: Manual Build on VPS (Simplest)

**Use when**: First deployment, testing, or one-time setup

### Pros
✅ Simple setup (just run one script)
✅ No GitHub secrets needed
✅ No local Docker required

### Cons
❌ VPS needs 8GB+ RAM (Rust compilation)
❌ Slow builds (20-30 minutes)
❌ Builds can slow down production

### How to Use

```bash
# 1. Clone on VPS
ssh user@your-vps
git clone https://github.com/YOUR-USERNAME/revolt.git
cd revolt

# 2. Configure
cp .env.prod.example .env.prod
nano .env.prod  # Fill in secrets

# 3. Setup SSL
sudo certbot certonly --standalone -d chat.armabattles.com
mkdir -p nginx/ssl
sudo cp /etc/letsencrypt/live/chat.armabattles.com/fullchain.pem nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/chat.armabattles.com/privkey.pem nginx/ssl/key.pem

# 4. Deploy
./deploy.sh
```

**File used**: `docker-compose.prod.yml`

**Updates**:
```bash
git pull
docker compose -f docker-compose.prod.yml up -d --build
```

---

## ⚡ Option 2: CI/CD with GitHub Actions (Recommended)

**Use when**: Production deployment, working in a team

### Pros
✅ **Automatic deployments** on git push
✅ VPS only needs 4GB RAM
✅ Fast updates (5 minutes)
✅ Parallel builds on GitHub servers
✅ Free for public repos
✅ Zero-downtime deployments
✅ Automatic tests before deploy

### Cons
❌ More complex initial setup
❌ Requires GitHub secrets configuration
❌ Uses GitHub Actions minutes (free tier: 2000 min/month)

### How to Use

**1. Complete Setup** (one-time):
```bash
# Read the full guide
cat CI-CD-SETUP.md

# Or quick version:
# - Generate SSH key for GitHub Actions
# - Add secrets to GitHub (VPS_SSH_KEY, VPS_HOST, VPS_USER)
# - Setup VPS deployment directory
# - Update docker-compose.prod-ci.yml with your GitHub username
```

**2. Deploy**:
```bash
# Just push code!
git add .
git commit -m "Update feature"
git push origin main

# Watch build at: github.com/YOUR-USERNAME/YOUR-REPO/actions
```

**File used**: `docker-compose.prod-ci.yml`

**Full guide**: See `CI-CD-SETUP.md`

---

## 🏗️ Option 3: Build Locally, Deploy Images (Advanced)

**Use when**: Solo developer, want control, prefer local builds

### Pros
✅ VPS only needs 4GB RAM
✅ Build on powerful local machine
✅ Full control over build process
✅ No GitHub Actions minutes used
✅ Works offline

### Cons
❌ Requires Docker Desktop on Windows (8GB+ RAM)
❌ Manual image push/pull
❌ More steps per deployment

### How to Use

**1. Build images locally**:
```bash
# On your Windows machine
cd C:\revolt

# Build all images
docker compose -f docker-compose.prod.yml build

# Tag for registry
docker tag armabattles-delta your-dockerhub/chat-delta:latest
docker tag armabattles-bonfire your-dockerhub/chat-bonfire:latest
docker tag armabattles-autumn your-dockerhub/chat-autumn:latest
docker tag armabattles-january your-dockerhub/chat-january:latest
docker tag armabattles-gifbox your-dockerhub/chat-gifbox:latest
docker tag armabattles-frontend your-dockerhub/chat-frontend:latest

# Push to Docker Hub
docker push your-dockerhub/chat-delta:latest
docker push your-dockerhub/chat-bonfire:latest
docker push your-dockerhub/chat-autumn:latest
docker push your-dockerhub/chat-january:latest
docker push your-dockerhub/chat-gifbox:latest
docker push your-dockerhub/chat-frontend:latest
```

**2. Update VPS docker-compose.yml**:
```yaml
services:
  delta:
    image: your-dockerhub/chat-delta:latest
    # Remove build: section
```

**3. Deploy on VPS**:
```bash
ssh user@your-vps
cd /opt/armabattles-chat

# Pull images
docker compose -f docker-compose.prod.yml pull

# Restart
docker compose -f docker-compose.prod.yml up -d
```

---

## 🎯 Recommendation by Use Case

### First-Time Deployment
**Use Option 1** (Manual):
- Simple to understand
- Get production running quickly
- Learn how everything works

### After Initial Setup
**Migrate to Option 2** (CI/CD):
- Set up GitHub Actions (1 hour one-time setup)
- Enjoy automated deployments forever
- Best long-term solution

### Solo Developer (No Team)
**Option 3** (Local Build) is also fine:
- Full control
- No reliance on GitHub
- Works offline

---

## 🔄 Migration Between Options

### From Manual → CI/CD

```bash
# 1. Follow CI-CD-SETUP.md
# 2. Copy docker-compose.prod-ci.yml to VPS
# 3. Update YOUR-USERNAME in the file
# 4. Push code to trigger first CI/CD build
```

### From Local Build → CI/CD

```bash
# 1. Setup GitHub Actions (CI-CD-SETUP.md)
# 2. Remove manual build steps from your workflow
# 3. Just push code to deploy
```

### From CI/CD → Manual

```bash
# 1. On VPS, switch back to docker-compose.prod.yml
# 2. Run ./deploy.sh to build locally
```

---

## 📋 Quick Decision Tree

```
Do you have a team or need frequent deployments?
├─ YES → Option 2 (CI/CD) ⚡
└─ NO
    ├─ Is this your first deployment?
    │   ├─ YES → Option 1 (Manual) 🛠️
    │   └─ NO → Option 3 (Local Build) 🏗️
    │
    └─ Do you want zero-downtime deploys?
        └─ YES → Option 2 (CI/CD) ⚡
```

---

## 🆘 Getting Help

- **Manual deployment**: See `DEPLOYMENT.md`
- **CI/CD setup**: See `CI-CD-SETUP.md`
- **Local build issues**: Check Docker Desktop resources (8GB+ RAM)
- **VPS issues**: Check `docker compose logs`

---

## 💡 Pro Tips

### Hybrid Approach
1. Start with **Option 1** (Manual) to get running
2. Setup **Option 2** (CI/CD) once stable
3. Use CI/CD for regular updates
4. Keep manual deployment as backup

### Cost Optimization
- **CI/CD is FREE** for public repos (2000 min/month)
- Private repos: 2000 free minutes = ~40-50 deployments/month
- Option 3 (Local Build) uses your PC resources, no cloud costs

### Performance
- **Fastest updates**: Option 2 (CI/CD) - 5 minutes
- **Fastest first deployment**: Option 1 (Manual) - one command
- **Most reliable**: Option 2 (CI/CD) - automated tests

---

**Choose your path and get deploying! 🚀**
