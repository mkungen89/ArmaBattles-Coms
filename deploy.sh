#!/bin/bash
# Arma Battles Chat - Quick Deployment Script

set -e

echo "🚀 Arma Battles Chat - Production Deployment"
echo "============================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if .env.prod exists
if [ ! -f .env.prod ]; then
    echo -e "${RED}Error: .env.prod not found!${NC}"
    echo "Please copy .env.prod.example to .env.prod and configure it."
    echo "Run: cp .env.prod.example .env.prod"
    exit 1
fi

# Check if SSL certificates exist
if [ ! -f nginx/ssl/cert.pem ] || [ ! -f nginx/ssl/key.pem ]; then
    echo -e "${YELLOW}Warning: SSL certificates not found in nginx/ssl/${NC}"
    echo "Please setup SSL certificates before deployment."
    echo "See DEPLOYMENT.md for instructions."
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Load environment variables
echo -e "${GREEN}✓${NC} Loading environment variables..."
export $(cat .env.prod | grep -v '^#' | xargs)

# Check if services are already running
if docker compose -f docker-compose.prod.yml ps | grep -q "Up"; then
    echo -e "${YELLOW}Services are already running.${NC}"
    read -p "Do you want to rebuild and restart? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Stopping services..."
        docker compose -f docker-compose.prod.yml down
    else
        exit 0
    fi
fi

# Build services
echo ""
echo -e "${GREEN}Building services...${NC}"
echo "This may take 10-30 minutes on first build."
docker compose -f docker-compose.prod.yml build

# Start services
echo ""
echo -e "${GREEN}Starting services...${NC}"
docker compose -f docker-compose.prod.yml up -d

# Wait for services to be healthy
echo ""
echo -e "${GREEN}Waiting for services to be healthy...${NC}"
sleep 10

# Check service status
echo ""
echo -e "${GREEN}Service Status:${NC}"
docker compose -f docker-compose.prod.yml ps

# Check if all services are running
if docker compose -f docker-compose.prod.yml ps | grep -q "Exit\|Restarting"; then
    echo ""
    echo -e "${RED}⚠ Some services failed to start!${NC}"
    echo "Check logs with: docker compose -f docker-compose.prod.yml logs"
    exit 1
fi

# Success message
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ Deployment successful!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Your Arma Battles Chat is now running!"
echo ""
echo "📱 Access your chat at: https://chat.armabattles.com"
echo ""
echo "Useful commands:"
echo "  • View logs:    docker compose -f docker-compose.prod.yml logs -f"
echo "  • Stop:         docker compose -f docker-compose.prod.yml down"
echo "  • Restart:      docker compose -f docker-compose.prod.yml restart"
echo "  • Status:       docker compose -f docker-compose.prod.yml ps"
echo ""
echo "📖 Full documentation: See DEPLOYMENT.md"
echo ""
