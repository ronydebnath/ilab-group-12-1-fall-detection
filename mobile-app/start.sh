#!/bin/bash
set -e

echo "Starting initialization..."

# Check if we need to initialize the project
if [ ! -f "package.json" ]; then
    echo "Initializing new Expo project..."
    npx create-expo-app . --template blank --yes
    npm install
fi

# Ensure node_modules exists
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

# Install web dependencies if not present
if ! grep -q "react-native-web" package.json; then
    echo "Installing web dependencies..."
    npm install react-native-web react-dom @expo/webpack-config
fi

echo "Starting Expo server..."
exec npx expo start --host localhost --port 19002