# Changelog

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
