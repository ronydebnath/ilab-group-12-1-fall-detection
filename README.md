# Fall Detection with IoT and Swarm Learning

## Overview
Falls pose a significant risk to elderly individuals, often leading to injuries and delayed medical assistance. This project aims to develop a real-time, low-cost fall detection system using IoT devices, such as Wear OS smartwatches and Android phones. The system leverages local sensor data processing and Swarm Learning to enhance detection accuracy while ensuring user privacy.

## Features
- **Real-time Fall Detection**: Uses accelerometer and gyroscope data from Wear OS smartwatches and Android devices to detect falls instantly.
- **Emergency Alerts**: Sends immediate notifications upon detecting a fall to caregivers or emergency contacts.
- **Privacy-Preserving AI**: Implements Swarm Learning, allowing devices to improve detection models collaboratively without sharing raw sensor data.
- **Edge Computing**: Processes sensor data locally, reducing reliance on cloud services for faster and more reliable detection.
- **Cross-platform Mobile App**: Modern Expo/React Native app for Android, iOS, and web.

## Technology Stack
- **Programming Language**: Python (backend, ML), JavaScript/TypeScript (mobile app)
- **IoT Devices**: Wear OS Smartwatches, Android Phones
- **Machine Learning Frameworks**: TensorFlow, PyTorch
- **Swarm Learning**: Decentralized model training
- **Poetry**: Dependency management and packaging (backend)
- **Expo/React Native**: Mobile app development
- **Docker & Docker Compose**: Containerized development and deployment

## Project Structure
```
iLab_Group_12-1-fall-detection/
│
├── backend/                        # Backend (Laravel/PHP) application
│   ├── app/                        # Laravel app code (controllers, models, etc.)
│   ├── bootstrap/                  # Laravel bootstrap files
│   ├── config/                     # Laravel and custom config files
│   ├── database/                   # Migrations, seeders, factories
│   ├── docker/
│   │   └── nginx/
│   │       └── conf.d/             # Nginx config for backend (app.conf, app.conf.example)
│   ├── docs/                       # Backend-specific documentation
│   ├── public/                     # Public web root
│   ├── resources/                  # Views, language files, etc.
│   ├── routes/                     # API and web routes
│   ├── storage/                    # Storage (logs, uploads, etc.)
│   ├── tests/                      # Backend unit and feature tests
│   ├── vendor/                     # Composer dependencies
│   ├── composer.json               # Composer dependencies config
│   ├── composer.lock               # Composer lock file
│   ├── Dockerfile                  # Backend Docker build
│   ├── package.json                # Node dependencies for Laravel Mix/Vite
│   ├── phpunit.xml                 # PHPUnit config
│   ├── setup.sh                    # Backend setup script
│   └── vite.config.js              # Vite config for frontend assets
│
├── mobile-app/                     # Expo/React Native mobile app
│   ├── App.js                      # App entry point, wraps navigation and context providers
│   ├── Dockerfile                  # Docker build for Expo app
│   ├── start.sh                    # Startup script for Docker/CI
│   ├── package.json                # NPM dependencies and scripts
│   ├── app.json                    # Expo configuration
│   ├── src/
│   │   ├── screens/                # App screens (Home, Login, Register, Settings, Alert)
│   │   ├── navigation/             # Navigation stack and logic
│   │   ├── contexts/               # React context providers (Auth, App)
│   │   ├── api/                    # API service layer
│   │   ├── constants/              # App-wide constants
│   │   ├── styles/                 # Shared styles
│   │   ├── services/               # Utility services (sound, permissions, etc.)
│   │   └── ...
│   ├── assets/                     # Images, fonts, etc.
│   ├── .expo/                      # Expo local data
│   ├── node_modules/               # Node dependencies
│   ├── tsconfig.json               # TypeScript config
│   ├── eslint.config.js            # ESLint config
│   ├── README.md                   # Mobile app documentation
│   └── ...                         # Other config and dotfiles
│
├── notebooks/                      # Jupyter notebooks for data analysis and ML
│   ├── Data_Prep_DL.ipynb
│   ├── Data_Prep_CAGE.ipynb
│   ├── Binary-CNN - Pipeline.ipynb
│   ├── Binary-CNN - Pipeline-FineTuning.ipynb
│   ├── Activity-window-streamlit.ipynb
│   ├── sample_fall_window.ipynb
│   ├── ...                         # More notebooks and subfolders
│
├── streamlit-app/                  # Streamlit web app for visualization or demo
│   ├── app.py
│   ├── app_2.py
│   ├── app_3.py
│   ├── models/
│   ├── samples/
│   └── ...
│
├── swarm_learning/                 # Swarm learning/federated learning code
│   └── ...                         # Python modules for decentralized training
│
├── storage/                        # Shared storage (e.g., for models, uploads)
│   └── ...
│
├── data/                           # Datasets and data files
│   └── ...
│
├── docs/                           # Project-wide documentation
│   ├── architecture.md
│   ├── deployment.md
│   ├── testing.md
│   └── troubleshooting.md
│
├── tests/                          # Project-wide or legacy tests
│   └── ...
│
├── src/                            # (Legacy or additional) Python backend code
│   └── ilab_group_12_1_fall_detection/
│
├── docker-compose.yml              # Multi-service orchestration
├── Dockerfile                      # (Root) Dockerfile, if present
├── pyproject.toml                  # Poetry config for Python backend
├── poetry.lock                     # Poetry lock file
├── LICENSE                         # Project license
├── changelog.md                    # Changelog
├── .gitignore                      # Git ignore rules
├── .dockerignore                   # Docker ignore rules
├── README.md                       # Project documentation (this file)
└── ...
```
## Contributing
We welcome contributions! Please follow these steps:
1. Fork the repository
2. Create a new branch (`git checkout -b feature-branch`)
3. Commit your changes (`git commit -m "Add new feature"`)
4. Push to the branch (`git push origin feature-branch`)
5. Create a Pull Request

## License
This project is licensed under the MIT License. See the `LICENSE` file for details.

## Contact
For any inquiries, please contact:
- **Drishya Chuke**: [your.email@example.com](mailto:your.email@example.com)
- **GitHub**: [your-github-profile](https://github.com/your-github-profile)

- **Katherin Gomez Londono**: [your.email@example.com](mailto:your.email@example.com)
- **GitHub**: [your-github-profile](https://github.com/your-github-profile)

- **Sidhant Bajaj**: [your.email@example.com](mailto:your.email@example.com)
- **GitHub**: [your-github-profile](https://github.com/your-github-profile)

- **Vishwas Singh**: [your.email@example.com](mailto:your.email@example.com)
- **GitHub**: [your-github-profile](https://github.com/your-github-profile)

- **Rony Debnath**: [your.email@example.com](mailto:your.email@example.com)
- **GitHub**: [your-github-profile](https://github.com/your-github-profile)

- **Ratana Sovann**: [your.email@example.com](mailto:your.email@example.com)
- **GitHub**: [your-github-profile](https://github.com/your-github-profile)

---
Developed with ❤️ for safer elderly care.
