# Use Node.js 18 Alpine as base image
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    python3 \
    make \
    g++ \
    git \
    bash

# Copy the start script first
COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application
COPY . .

# Set environment variables
ENV NODE_ENV=development
ENV REACT_NATIVE_PACKAGER_HOSTNAME=localhost
ENV EXPO_DEVTOOLS_LISTEN_ADDRESS=0.0.0.0
ENV EXPO_PACKAGER_PROXY_URL=http://localhost:19000

# Expose Expo ports
EXPOSE 19000 19001 19002

# Start the Expo development server
CMD ["/bin/bash", "/app/start.sh"] 