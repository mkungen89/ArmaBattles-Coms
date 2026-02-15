# Nginx Configuration for Arma Battles Chat

This directory contains the Nginx reverse proxy configuration.

## SSL Certificates

You need to place your SSL certificates in the `ssl/` subdirectory:

```
nginx/
├── nginx.conf
└── ssl/
    ├── cert.pem    (your SSL certificate)
    └── key.pem     (your private key)
```

### Option 1: Let's Encrypt (Recommended for Production)

```bash
# Install Certbot
sudo apt install certbot -y

# Get certificate
sudo certbot certonly --standalone -d chat.armabattles.com

# Copy to nginx/ssl
mkdir -p ssl
sudo cp /etc/letsencrypt/live/chat.armabattles.com/fullchain.pem ssl/cert.pem
sudo cp /etc/letsencrypt/live/chat.armabattles.com/privkey.pem ssl/key.pem
sudo chmod 644 ssl/*.pem
```

### Option 2: Self-Signed Certificate (Development Only)

```bash
mkdir -p ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout ssl/key.pem \
  -out ssl/cert.pem \
  -subj "/CN=chat.armabattles.com"
```

## Configuration

The `nginx.conf` file is configured to:

- ✅ Redirect HTTP to HTTPS
- ✅ Proxy frontend requests to the frontend container
- ✅ Proxy API requests to Delta (REST API)
- ✅ Proxy WebSocket connections to Bonfire
- ✅ Proxy file uploads/downloads to Autumn
- ✅ Proxy image processing to January
- ✅ Proxy GIF requests to Gifbox
- ✅ Enable gzip compression
- ✅ Add security headers
- ✅ Rate limiting for API endpoints

## Testing Configuration

Before starting the full stack, you can test the Nginx configuration:

```bash
# Test configuration syntax
docker run --rm -v $(pwd)/nginx.conf:/etc/nginx/nginx.conf:ro nginx nginx -t

# Or if nginx is already running
docker exec armabattles-nginx nginx -t
```

## Customization

If you need to customize the configuration:

1. Edit `nginx.conf`
2. Test the configuration (see above)
3. Reload Nginx:
   ```bash
   docker exec armabattles-nginx nginx -s reload
   ```

## Troubleshooting

### Certificate Issues

If you get SSL certificate errors:

```bash
# Check certificate validity
openssl x509 -in ssl/cert.pem -text -noout

# Check if certificate matches domain
openssl x509 -in ssl/cert.pem -noout -subject
```

### Connection Issues

```bash
# Check Nginx logs
docker compose -f docker-compose.prod.yml logs nginx

# Check if Nginx can reach backend services
docker exec armabattles-nginx ping frontend
docker exec armabattles-nginx ping delta
```

### 502 Bad Gateway

Usually means backend services aren't running:

```bash
# Check service status
docker compose -f docker-compose.prod.yml ps

# Check specific service logs
docker compose -f docker-compose.prod.yml logs delta
```
