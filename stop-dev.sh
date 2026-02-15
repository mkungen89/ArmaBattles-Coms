#!/bin/bash
# Arma Battles Chat - Development Stop Script

echo "🛑 Stopping Arma Battles Chat Development Environment..."

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

# Stop Rust processes if PIDs file exists
if [ -f .dev-pids ]; then
    echo -e "${RED}🔴 Stopping backend services...${NC}"
    while IFS= read -r pid; do
        if ps -p "$pid" > /dev/null 2>&1; then
            kill "$pid" 2>/dev/null
            echo "  ├─ Stopped process $pid"
        fi
    done < .dev-pids
    rm .dev-pids
else
    echo "⚠️  No PID file found. Trying to kill by name..."
    pkill -f "revolt-delta" 2>/dev/null
    pkill -f "revolt-bonfire" 2>/dev/null
    pkill -f "revolt-autumn" 2>/dev/null
    pkill -f "revolt-january" 2>/dev/null
fi

echo ""
echo -e "${RED}🐳 Stopping Docker services...${NC}"
docker compose down

echo ""
echo -e "${GREEN}✅ All services stopped!${NC}"
