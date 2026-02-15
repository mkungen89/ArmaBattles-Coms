#!/bin/bash
# Arma Battles Chat - Development Start Script

echo "🚀 Starting Arma Battles Chat Development Environment..."
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Export PATH for Rust
export PATH="$HOME/.cargo/bin:$PATH"

# Check if services are running
echo -e "${BLUE}📊 Checking Docker services...${NC}"
docker compose ps

echo ""
echo -e "${BLUE}🔥 Starting backend services (MongoDB, Redis, etc.)...${NC}"
docker compose up -d mongodb redis minio rabbitmq maildev

echo ""
echo -e "${YELLOW}⏳ Waiting for services to be ready...${NC}"
sleep 5

echo ""
echo -e "${BLUE}🦀 Starting Rust backend servers...${NC}"
cd arma-backend

# Start all backend services in background
echo "  ├─ Starting Delta (API) on port 14702..."
cargo run --bin revolt-delta &
DELTA_PID=$!

echo "  ├─ Starting Bonfire (WebSocket) on port 14703..."
cargo run --bin revolt-bonfire &
BONFIRE_PID=$!

echo "  ├─ Starting Autumn (Files) on port 14704..."
cargo run --bin revolt-autumn &
AUTUMN_PID=$!

echo "  └─ Starting January (Proxy) on port 14705..."
cargo run --bin revolt-january &
JANUARY_PID=$!

cd ..

echo ""
echo -e "${GREEN}✅ Backend services starting...${NC}"
echo ""
echo -e "${BLUE}📝 Process IDs:${NC}"
echo "  Delta:    $DELTA_PID"
echo "  Bonfire:  $BONFIRE_PID"
echo "  Autumn:   $AUTUMN_PID"
echo "  January:  $JANUARY_PID"
echo ""
echo -e "${GREEN}🎉 Development environment is ready!${NC}"
echo ""
echo "📍 Access points:"
echo "  Frontend:     http://localhost:3000"
echo "  API:          http://localhost:14702"
echo "  WebSocket:    ws://localhost:14703"
echo "  Files:        http://localhost:14704"
echo "  MinIO:        http://localhost:14010"
echo "  RabbitMQ:     http://localhost:15672"
echo "  Maildev:      http://localhost:14080"
echo ""
echo "🛑 To stop all services, run: ./stop-dev.sh"
echo ""

# Save PIDs to file for stop script
echo "$DELTA_PID" > .dev-pids
echo "$BONFIRE_PID" >> .dev-pids
echo "$AUTUMN_PID" >> .dev-pids
echo "$JANUARY_PID" >> .dev-pids

# Keep script running
echo "Press Ctrl+C to stop all services..."
wait
