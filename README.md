# Fall Detection with IoT and Swarm Learning

## Overview
Falls pose a significant risk to elderly individuals, often leading to injuries and delayed medical assistance. This project aims to develop a real-time, low-cost fall detection system using IoT devices, such as Wear OS smartwatches and Android phones. The system leverages local sensor data processing and Swarm Learning to enhance detection accuracy while ensuring user privacy.

## Features
- **Real-time Fall Detection**: Uses accelerometer and gyroscope data from Wear OS smartwatches and Android devices to detect falls instantly.
- **Emergency Alerts**: Sends immediate notifications upon detecting a fall to caregivers or emergency contacts.
- **Privacy-Preserving AI**: Implements Swarm Learning, allowing devices to improve detection models collaboratively without sharing raw sensor data.
- **Edge Computing**: Processes sensor data locally, reducing reliance on cloud services for faster and more reliable detection.

## Technology Stack
- **Programming Language**: Python
- **IoT Devices**: Wear OS Smartwatches, Android Phones
- **Machine Learning Frameworks**: TensorFlow, PyTorch
- **Swarm Learning**: Decentralized model training using federated learning techniques
- **Poetry**: Dependency management and packaging

## Installation
To set up the project environment, ensure you have **Poetry** installed. Then, run the following commands:

```sh
# Clone the repository
git clone https://github.com/ronydebnath/ilab-group-12-1-fall-detection.git
cd ilab-group-12-1-fall-detection

# Install dependencies using Poetry
poetry install
```

## Usage
To run the fall detection system, execute the following command:

```sh
poetry run python main.py
```

## Project Structure
```
iLab_Group_12-1-fall-detection/
│── src/                   # Source code
│   ├── models/            # ML models for fall detection
│   ├── sensors/           # Sensor data processing modules
│   ├── notifications/     # Emergency alert system
│   ├── swarm_learning/    # Decentralized training implementation
│   ├── main.py            # Entry point of the application
│── tests/                 # Unit tests
│── pyproject.toml         # Poetry configuration
│── README.md              # Project documentation
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
