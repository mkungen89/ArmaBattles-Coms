# 🚀 CI/CD Pipeline Setup Guide

This guide explains how to set up automated builds and deployments using GitHub Actions.

## 📋 Overview

When you push code to GitHub:
1. ✅ **Tests run automatically** (backend tests, frontend linting)
2. 🏗️ **Docker images build** (all services in parallel)
3. 📦 **Images push to GitHub Container Registry** (ghcr.io)
4. 🚀 **Auto-deploy to VPS** (if pushed to main/production branch)

## 🔧 Setup Steps

### 1. Generate SSH Key for VPS Deployment

On your **local machine**:

```bash
# Generate new SSH key pair
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_vps

# This creates:
# - ~/.ssh/github_actions_vps (private key - add to GitHub)
# - ~/.ssh/github_actions_vps.pub (public key - add to VPS)
```

### 2. Add Public Key to VPS

```bash
# Copy public key to VPS
ssh-copy-id -i ~/.ssh/github_actions_vps.pub user@your-vps-ip

# Or manually:
cat ~/.ssh/github_actions_vps.pub
# Then on VPS:
echo "paste-public-key-here" >> ~/.ssh/authorized_keys
```

### 3. Configure GitHub Secrets

Go to your GitHub repository: **Settings → Secrets and variables → Actions → New repository secret**

Add these secrets:

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `VPS_SSH_KEY` | Contents of `~/.ssh/github_actions_vps` | Private SSH key (entire file content) |
| `VPS_HOST` | `your-vps-ip-address` | Your VPS IP or domain |
| `VPS_USER` | `username` | SSH user on VPS (e.g., `ubuntu`, `root`) |
| `OAUTH_CLIENT_ID` | `019c5d06-b3f3-709a-a212-b4441d609080` | OAuth client ID |

**To get private key content:**
```bash
# On Windows (Git Bash):
cat ~/.ssh/github_actions_vps | clip

# On Linux/Mac:
cat ~/.ssh/github_actions_vps | pbcopy  # Mac
cat ~/.ssh/github_actions_vps | xclip   # Linux

# Then paste into GitHub secret
```

### 4. Prepare VPS for Deployment

SSH into your VPS and set up the deployment directory:

```bash
# SSH to VPS
ssh user@your-vps

# Create deployment directory
sudo mkdir -p /opt/armabattles-chat
sudo chown $USER:$USER /opt/armabattles-chat
cd /opt/armabattles-chat

# Clone repository
git clone https://github.com/YOUR-USERNAME/YOUR-REPO.git .

# Create .env.prod file
cp .env.prod.example .env.prod
nano .env.prod  # Fill in all secrets

# Create SSL directory
mkdir -p nginx/ssl
# Setup SSL certificates (see DEPLOYMENT.md)

# Install GitHub Container Registry authentication
echo $GITHUB_TOKEN | docker login ghcr.io -u YOUR-GITHUB-USERNAME --password-stdin

# Note: You'll need a Personal Access Token with read:packages permission
# Create at: https://github.com/settings/tokens
```

### 5. Update docker-compose.prod.yml for GHCR

Edit `docker-compose.prod.yml` to use GitHub Container Registry images:

```yaml
services:
  delta:
    image: ghcr.io/YOUR-USERNAME/armabattles-chat-delta:latest
    # Remove 'build:' section

  bonfire:
    image: ghcr.io/YOUR-USERNAME/armabattles-chat-bonfire:latest
    # Remove 'build:' section

  autumn:
    image: ghcr.io/YOUR-USERNAME/armabattles-chat-autumn:latest
    # Remove 'build:' section

  january:
    image: ghcr.io/YOUR-USERNAME/armabattles-chat-january:latest
    # Remove 'build:' section

  gifbox:
    image: ghcr.io/YOUR-USERNAME/armabattles-chat-gifbox:latest
    # Remove 'build:' section

  frontend:
    image: ghcr.io/YOUR-USERNAME/armabattles-chat-frontend:latest
    # Remove 'build:' section
```

### 6. Test the Pipeline

```bash
# On your local machine:
git add .
git commit -m "Setup CI/CD pipeline"
git push origin main

# Watch the build on GitHub:
# https://github.com/YOUR-USERNAME/YOUR-REPO/actions
```

## 🔄 How It Works

### Workflow Stages

```
┌─────────────────────────────────────────────────────────────┐
│  1. PUSH CODE                                               │
│     git push origin main                                    │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  2. RUN TESTS (parallel)                                    │
│     ✓ Backend: cargo test                                  │
│     ✓ Frontend: yarn lint, yarn typecheck                  │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  3. BUILD IMAGES (parallel)                                 │
│     ✓ Delta     (Rust API)         → ghcr.io/.../delta    │
│     ✓ Bonfire   (WebSocket)        → ghcr.io/.../bonfire  │
│     ✓ Autumn    (Files)            → ghcr.io/.../autumn   │
│     ✓ January   (Proxy)            → ghcr.io/.../january  │
│     ✓ Gifbox    (GIFs)             → ghcr.io/.../gifbox   │
│     ✓ Frontend  (Preact SPA)       → ghcr.io/.../frontend │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  4. PUSH TO REGISTRY                                        │
│     All images → ghcr.io (GitHub Container Registry)       │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  5. DEPLOY TO VPS (if main/production branch)               │
│     SSH → Pull images → Restart containers → Health check  │
└─────────────────────────────────────────────────────────────┘
```

### Build Time Estimate

- **Backend services**: ~15-20 minutes (Rust compilation)
- **Frontend**: ~5-10 minutes (Node build)
- **Total pipeline**: ~20-30 minutes

**Caching reduces this to ~5-10 minutes on subsequent runs!**

## 📦 GitHub Container Registry

Your Docker images will be available at:

```
ghcr.io/YOUR-USERNAME/armabattles-chat-delta:latest
ghcr.io/YOUR-USERNAME/armabattles-chat-bonfire:latest
ghcr.io/YOUR-USERNAME/armabattles-chat-autumn:latest
ghcr.io/YOUR-USERNAME/armabattles-chat-january:latest
ghcr.io/YOUR-USERNAME/armabattles-chat-gifbox:latest
ghcr.io/YOUR-USERNAME/armabattles-chat-frontend:latest
```

View them at: `https://github.com/YOUR-USERNAME?tab=packages`

## 🎯 Usage

### Deploy to Production

```bash
# Just push to main branch:
git push origin main

# Or manually trigger:
# GitHub → Actions → Build and Deploy to VPS → Run workflow
```

### Deploy to Staging

```bash
# Create a staging branch
git checkout -b staging
git push origin staging

# Update workflow to include staging:
# on:
#   push:
#     branches: [ main, production, staging ]
```

### Manual Deployment (Bypass CI/CD)

```bash
# SSH to VPS
ssh user@your-vps
cd /opt/armabattles-chat

# Pull and restart
git pull
docker compose -f docker-compose.prod.yml pull
docker compose -f docker-compose.prod.yml up -d
```

## 🔍 Monitoring Deployments

### View Build Logs

1. Go to **GitHub → Actions** tab
2. Click on latest workflow run
3. View individual job logs

### Check Deployment Status

```bash
# On VPS:
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f
```

### Health Checks

The workflow automatically checks:
```bash
curl -f https://chat.armabattles.com/api/
```

If health check fails, deployment rolls back.

## 🛑 Rollback

### Automatic Rollback

If health check fails, the old containers keep running (zero-downtime deployment).

### Manual Rollback

```bash
# On VPS:
cd /opt/armabattles-chat

# Pull specific version
docker compose -f docker-compose.prod.yml pull delta:sha-abc123

# Or checkout old code
git checkout <previous-commit-hash>
docker compose -f docker-compose.prod.yml up -d
```

## 🔐 Security Best Practices

### GitHub Secrets
- ✅ Never commit secrets to repository
- ✅ Use GitHub Secrets for all sensitive data
- ✅ Rotate SSH keys regularly
- ✅ Use minimal permissions for deploy user on VPS

### VPS Security
```bash
# Create dedicated deploy user (recommended)
sudo adduser github-deploy
sudo usermod -aG docker github-deploy

# Use this user in VPS_USER secret
```

### Image Security
```bash
# Scan images for vulnerabilities
docker scan ghcr.io/YOUR-USERNAME/armabattles-chat-frontend:latest
```

## 🐛 Troubleshooting

### Build Fails

**Check logs:**
```
GitHub → Actions → Failed workflow → View logs
```

**Common issues:**
- Submodule not checked out → Add `submodules: recursive` to checkout step
- Out of memory → Increase GitHub runner memory or use self-hosted runner
- Rust compilation fails → Check Cargo.toml dependencies

### Deployment Fails

**SSH connection refused:**
- Check VPS_SSH_KEY is correct private key (entire file)
- Check VPS_HOST and VPS_USER are correct
- Verify SSH key is added to VPS `~/.ssh/authorized_keys`

**Docker pull fails:**
- Ensure GitHub Container Registry is public or VPS is authenticated
- Run on VPS: `docker login ghcr.io -u YOUR-USERNAME`

**Services don't start:**
- Check .env.prod exists on VPS
- Check SSL certificates exist in nginx/ssl/
- View logs: `docker compose -f docker-compose.prod.yml logs`

### Health Check Fails

```bash
# On VPS, manually check:
docker compose -f docker-compose.prod.yml ps  # All running?
docker compose -f docker-compose.prod.yml logs delta  # Any errors?
curl http://localhost:14702/  # API responding?
```

## 📊 Advanced: Self-Hosted Runner

For faster builds and no GitHub Actions minutes usage:

### Setup Self-Hosted Runner

1. **GitHub → Settings → Actions → Runners → New self-hosted runner**
2. Follow setup instructions on a VPS/server
3. Update workflow:

```yaml
jobs:
  build-backend:
    runs-on: self-hosted  # Instead of ubuntu-latest
```

**Benefits:**
- ⚡ Faster builds (local cache)
- 💰 No GitHub Actions minutes limit
- 🔧 Full control over build environment

**Requirements:**
- Dedicated build server (8GB+ RAM for Rust builds)
- Docker installed
- Always online

## 🎉 Benefits of This Setup

✅ **Automated Testing** - Bugs caught before deployment
✅ **Zero-Downtime Deployments** - Old containers run until new ones are healthy
✅ **Rollback Safety** - Easy to revert to previous version
✅ **Build Caching** - Fast subsequent builds
✅ **Parallel Builds** - All services build simultaneously
✅ **Image Registry** - Centralized image storage
✅ **Manual Control** - Can deploy manually when needed

## 📚 Further Reading

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [GitHub Container Registry](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
- [Docker Multi-stage Builds](https://docs.docker.com/build/building/multi-stage/)
- [Zero-Downtime Deployments](https://docs.docker.com/compose/production/)

---

**Questions?** Check the GitHub Actions logs or open an issue!
