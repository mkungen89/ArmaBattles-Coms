#!/bin/bash
echo "🔄 Restarting backend services with new changes..."

# Kill existing services
if [ -f .dev-pids ]; then
    echo "Stopping old services..."
    while read pid; do
        kill $pid 2>/dev/null && echo "  Stopped PID $pid" || echo "  PID $pid already stopped"
    done < .dev-pids
    rm .dev-pids
fi

# Wait a bit for ports to free up
sleep 2

# Restart
cd arma-backend

echo "Starting Delta (API)..."
cargo run --bin revolt-delta &
DELTA_PID=$!

echo "Starting Bonfire (WebSocket)..."
cargo run --bin revolt-bonfire &
BONFIRE_PID=$!

echo "Starting Autumn (Files)..."
cargo run --bin revolt-autumn &
AUTUMN_PID=$!

echo "Starting January (Proxy)..."
cargo run --bin revolt-january &
JANUARY_PID=$!

cd ..

# Save new PIDs
echo "$DELTA_PID" > .dev-pids
echo "$BONFIRE_PID" >> .dev-pids
echo "$AUTUMN_PID" >> .dev-pids
echo "$JANUARY_PID" >> .dev-pids

echo "✅ Backend restarted with new changes!"
echo "PIDs: Delta=$DELTA_PID, Bonfire=$BONFIRE_PID, Autumn=$AUTUMN_PID, January=$JANUARY_PID"
