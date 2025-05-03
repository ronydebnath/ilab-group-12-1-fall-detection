# Testing Documentation

## Overview
The test suite for the Swarm Learning system is designed to ensure the reliability and consistency of the distributed learning process. The tests cover model architecture, weight sharing, peer communication, and various edge cases.

## Test Structure

### 1. Model Architecture Tests (`test_model_architecture.py`)
- Verifies consistency between `node.py` and `node_server.py` model architectures
- Tests layer configurations and types
- Validates output shapes and model compilation parameters
- Ensures binary classification compatibility

### 2. Weight Sharing Tests (`test_weight_sharing.py`)
- Tests weight serialization and deserialization
- Validates noise addition to weights
- Tests weight averaging functionality
- Includes end-to-end weight sharing tests

### 3. Peer Communication Tests (`test_peer_communication.py`)
- Tests ZMQ-based peer communication
- Validates weight sending and receiving
- Tests peer tracking functionality
- Includes error handling tests

### 4. Test Configuration (`conftest.py`)
Provides common test fixtures:
- Model creation fixture
- Test data generation
- Environment variable setup
- ZMQ context and socket management

## Running Tests

### Basic Test Execution
```bash
# Run all tests
python -m pytest tests/

# Run specific test file
python -m pytest tests/test_model_architecture.py

# Run with verbose output
python -m pytest -v tests/

# Run with coverage report
python -m pytest --cov=swarm_learning tests/
```

### Test Environment Setup
The test suite requires:
- Python 3.8+
- TensorFlow 2.x
- PyZMQ
- pytest
- pytest-cov (for coverage reports)

### Environment Variables
Tests use the following environment variables:
- `NODE_ID`: Test node identifier
- `PEERS`: Comma-separated list of peer addresses
- `ZMQ_PORT`: Port for ZMQ communication
- `DATA_PATH`: Path to test data file

## Test Data
The test suite includes:
- Randomly generated test data with shape (100, 500, 9)
- Binary classification labels
- Simulated peer weights

## Best Practices
1. Always run the full test suite before deploying changes
2. Maintain test coverage above 80%
3. Add new tests for any new functionality
4. Update existing tests when modifying core functionality

## Troubleshooting
Common test issues and solutions:
1. ZMQ connection errors: Ensure no other process is using the test port
2. Memory issues: Reduce test data size if running on limited resources
3. Model mismatch errors: Verify model architecture consistency 