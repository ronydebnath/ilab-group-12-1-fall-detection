# Fall Detection Mobile App

This is the mobile application for the Fall Detection with IoT and Swarm Learning project. It is built with [Expo](https://expo.dev) (React Native) and is designed to run on Android, iOS, and the web. The app integrates with the backend and other services via Docker Compose for a seamless development and deployment experience.

---

## Features

- **Cross-platform**: Runs on Android, iOS, and web via Expo.
- **Authentication**: Secure login and registration.
- **Real-time Alerts**: Receives and displays fall alerts.
- **Sensor Integration**: Interfaces with device sensors for fall detection.
- **Swarm Learning**: Connects to the backend for collaborative model updates.
- **Modern UI**: Built with React Navigation and modular context providers.

---

## Project Structure

```
mobile-app/
│── App.js                  # App entry point, wraps navigation and context providers
│── Dockerfile              # Docker build for Expo app
│── start.sh                # Startup script for Docker/CI
│── package.json            # NPM dependencies and scripts
│── app.json                # Expo configuration
│── src/
│   ├── screens/            # App screens (Home, Login, Register, Settings, Alert)
│   ├── navigation/         # Navigation stack and logic
│   ├── contexts/           # React context providers (Auth, App)
│   ├── api/                # API service layer
│   ├── constants/          # App-wide constants
│   ├── styles/             # Shared styles
│   ├── services/           # Utility services (sound, permissions, etc.)
│── assets/                 # Images, fonts, etc.
```

---

## Prerequisites

- [Node.js](https://nodejs.org/) (v18+ recommended)
- [npm](https://www.npmjs.com/) or [yarn](https://yarnpkg.com/)
- [Docker](https://www.docker.com/) (for containerized development)
- [Expo CLI](https://docs.expo.dev/get-started/installation/) (optional for local dev)

---

## Local Development (without Docker)

1. **Install dependencies:**
   ```bash
   cd mobile-app
   npm install
   ```

2. **Start the Expo server:**
   ```bash
   npx expo start
   ```
   - Open the app in your browser, Android/iOS simulator, or Expo Go app.

3. **Project structure:**
   - Edit screens in `src/screens/`
   - Navigation is managed in `src/navigation/AppNavigator.js`
   - Context providers are in `src/contexts/`

---

## Running with Docker

The app can be run in a containerized environment, ideal for team development or CI/CD.

### **Build and Run the Mobile App Container**

```bash
docker-compose build mobile-app
docker-compose up mobile-app
```

- The Expo web server will be available at [http://localhost:19002](http://localhost:19002).
- The app will hot-reload on code changes if you mount the source as a volume.

### **How it works**

- The `Dockerfile` installs all dependencies and runs the `start.sh` script.
- `start.sh` ensures dependencies are installed and starts the Expo server.
- Ports `19000`, `19001`, and `19002` are exposed for Expo and web access.

### **Docker Compose Service Example**

```yaml
  mobile-app:
    build:
      context: ./mobile-app
      dockerfile: Dockerfile
    container_name: fall-detection-mobile-app
    restart: unless-stopped
    working_dir: /app
    volumes:
      - ./mobile-app:/app
      - mobile-app-node-modules:/app/node_modules
      - mobile-app-expo:/app/.expo
    networks:
      - fall-detection-network
    environment:
      - NODE_ENV=production
      - REACT_NATIVE_PACKAGER_HOSTNAME=localhost
      - EXPO_DEVTOOLS_LISTEN_ADDRESS=0.0.0.0
      - EXPO_PACKAGER_PROXY_URL=http://localhost:19000
    ports:
      - "19000:19000"
      - "19001:19001"
      - "19002:19002"
    depends_on:
      - backend-app
```

---

## Context Providers

- **AuthProvider**: Handles authentication state, login, logout, and registration.
- **AppProvider**: Manages global app state, fall alerts, and system status.
- Both are implemented in `src/contexts/` and must be imported and wrapped around your app in `App.js`.

---

## Troubleshooting

- **Expo not accessible**: Make sure ports `19000-19002` are not blocked and the container is running.
- **Dependencies not installing**: The `start.sh` script will auto-install missing dependencies on container start.

---

## Useful Commands

- **Reset Expo project:**
  ```bash
  npm run reset-project
  ```
- **Rebuild Docker image:**
  ```bash
  docker-compose build mobile-app
  ```
- **View logs:**
  ```bash
  docker-compose logs -f mobile-app
  ```

---

## Learn More

- [Expo Documentation](https://docs.expo.dev/)
- [React Navigation](https://reactnavigation.org/)
- [Docker Compose](https://docs.docker.com/compose/)

---

## License

This project is licensed under the MIT License.

---

Developed with ❤️ by iLab Group 12-1 for safer elderly care.