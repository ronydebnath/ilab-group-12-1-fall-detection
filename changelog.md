# Changelog

## [Unreleased] - 2025-04-22

### ✅ Added

- **Enhanced Weight Sharing Logging**
  - Added detailed logging of weight statistics including shapes, means, and standard deviations
  - Implemented progress tracking for weight aggregation
  - Added connection status monitoring for peer communication


### 🔧 Modified

- **Weight Sharing Protocol**
  - Enhanced error handling in weight transmission
  - Improved peer connection status tracking
  - Added detailed statistics for received and aggregated weights

- **Docker Configuration**
  - Updated base Python image to 3.9-slim
  - Optimized system dependencies installation
  - Improved Docker layer caching with requirements.txt copy
  - Changed exposed port from 5000 to 5555 for ZeroMQ communication
  - Updated peer connection URLs to use ZeroMQ format (node:port)
  - Reorganized port mappings in docker-compose.yml
  - Renamed network from "swarm-net" to "swarm-network"

- **Dependencies Management**
  - Downgraded pyzmq from 26.4.0 to 25.1.2 for better compatibility
  - Updated Python version from 3.11.9 to 3.11.3
  - Added/upgraded dependencies in requirements.txt for TensorFlow 2.16.1 compatibility

### 📁 Affected Files

- `node.py`
  - Added detailed weight statistics logging
  - Enhanced error handling for peer communication
  - Improved connection status tracking
  - Added data loading and preprocessing functions
  - Implemented model definition and utility functions
  - Added main training loop

- `node_server.py`
  - Added weight reception logging
  - Implemented aggregation progress tracking
  - Enhanced error reporting
  - Added ZeroMQ server logic for message handling
  - Implemented serialization helpers
  - Added server thread management

- `entrypoint.py`
  - Added main function for node configuration
  - Implemented background server process management
  - Added error handling and cleanup procedures

- `Dockerfile`
  - Updated base image and system dependencies
  - Optimized layer caching
  - Updated exposed port
  - Maintained entrypoint configuration

- `docker-compose.yml`
  - Updated peer connection URLs
  - Modified port mappings for all nodes
  - Reorganized service configurations
  - Updated network name

- `requirements.txt`
  - Updated package versions for compatibility
  - Added new dependencies
  - Optimized dependency management

---

## [Unreleased] - 2025-04-07

### ✅ Added

- **Fault Tolerance for Peer Connections**  
  - All peer communication attempts are now wrapped in `try/except` blocks.  
  - Nodes will continue training and communicating with available peers even if others are unreachable.

- **ZeroMQ Transport Layer**  
  - Replaced Flask-based HTTP communication with ZeroMQ sockets for improved scalability and lower latency.  
  - Introduced asynchronous message handling to support better parallelism and responsiveness.

- **Global Model Versioning**  
  - Introduced `model_version` tracking in global models.  
  - Version number increments with each successful global model update.

- **Differential Privacy Support**  
  - Implemented `add_noise()` function that adds Gaussian noise to model weights.  
  - Prepares the system for secure aggregation and privacy-preserving training workflows.

### 📁 Affected Files

- `entrypoint.py`  
  - Entry script initializing ZeroMQ server and node training loop.

- `node_server.py`  
  - New ZeroMQ server handling model reception and broadcast.

- `node.py`  
  - Local training logic, model weight noise injection, and peer-to-peer communication via ZeroMQ.

---
