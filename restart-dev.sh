#!/bin/bash
# Arma Battles Chat - Quick Restart Script

echo "🔄 Restarting Arma Battles Chat..."

./stop-dev.sh
sleep 2
./start-dev.sh
