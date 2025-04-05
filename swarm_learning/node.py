# node.py

# Import required libraries
import os
import time
import numpy as np
if not np.__version__.startswith("1."):
    raise RuntimeError(f"Incompatible NumPy version detected: {np.__version__}. Please use numpy<2.")

import tensorflow as tf
# Reduce CPU usage per container
tf.config.threading.set_intra_op_parallelism_threads(1)
tf.config.threading.set_inter_op_parallelism_threads(1)
import base64
import json
import zmq

# Configuration from environment variables
NODE_ID = os.environ.get('NODE_ID', 'node_default')

# Safely parse the PEERS variable into a list of peer addresses.
# Expected format: "node2:5555,node3:5555" (without any "http://" prefix)
peers_env = os.environ.get("PEERS", "")
if peers_env:
    PEERS = [p.strip() for p in peers_env.split(',') if p.strip()]
else:
    PEERS = []

ROUND_INTERVAL = 20  # Time delay between training rounds (in seconds)
ZMQ_PORT = 5555      # Default ZeroMQ communication port

# Load and preprocess the MNIST dataset
(x_train, y_train), (x_test, y_test) = tf.keras.datasets.mnist.load_data()
x_train, x_test = x_train / 255.0, x_test / 255.0                         # Normalize pixel values to [0, 1]
x_train = x_train.reshape(-1, 28 * 28)                                    # Flatten images to 784-dimensional vectors
x_test = x_test.reshape(-1, 28 * 28)

# Define a simple feedforward neural network model
model = tf.keras.Sequential([
    tf.keras.Input(shape=(784,)),
    tf.keras.layers.Dense(64, activation='relu'),         # Hidden layer with ReLU activation
    tf.keras.layers.Dense(10, activation='softmax')         # Output layer for 10 digit classes
])
model.compile(optimizer='adam', loss='sparse_categorical_crossentropy', metrics=['accuracy'])

# ----------------------------- Helper Functions -----------------------------

def serialize_weights(weights):
    """
    Serialize a list of NumPy arrays (model weights) into a base64-encoded JSON string.
    """
    return base64.b64encode(json.dumps([w.tolist() for w in weights]).encode()).decode()

def add_noise(weights, noise_std=0.01):
    """
    Adds Gaussian noise to model weights for differential privacy.
    The noise is added only for sharing, not for internal training.
    """
    return [w + np.random.normal(0, noise_std, size=w.shape) for w in weights]

def send_weights(peer, weights):
    """
    Sends the (noisy) model weights to the specified peer via ZeroMQ REQ/REP pattern.
    Uses a 3-second timeout to avoid blocking indefinitely.
    """
    context = zmq.Context()
    socket = context.socket(zmq.REQ)
    socket.RCVTIMEO = 3000  # Set timeout to 3000ms (3 seconds)
    try:
        # Connect to the peer directly. The peer should be in "hostname:port" format.
        socket.connect(f"tcp://{peer}")
        payload = {
            "type": "send_weight",
            "node_id": NODE_ID,
            "weights": serialize_weights(weights)
        }
        socket.send_json(payload)

        # Wait for reply with timeout
        reply = socket.recv_json()
        print(f"{NODE_ID}: Sent weights to {peer}, status: {reply['status']}")
    except zmq.error.Again:
        print(f"{NODE_ID}: Timeout waiting for reply from {peer}")
    except Exception as e:
        print(f"{NODE_ID}: Failed to contact {peer}: {e}")
    finally:
        socket.close()
        context.term()

# ----------------------------- Main Training Loop -----------------------------

if __name__ == "__main__":
    print(f"{NODE_ID}: Starting training loop...")
    while True:
        # Get current weights (pre-training snapshot, if needed)
        weights = model.get_weights()

        # For testing, use a small subset of data to reduce resource usage.
        x_train_small = x_train[:100]
        y_train_small = y_train[:100]
        print(f"{NODE_ID}: Before training")
        # model.fit(x_train_small, y_train_small, epochs=1, batch_size=32, verbose=0)
        # Train model locally for one epoch
        model.fit(x_train, y_train, epochs=1, batch_size=32, verbose=0)
        print(f"{NODE_ID}: After training")

        # Add noise to model weights before sharing for differential privacy.
        noisy_weights = add_noise(model.get_weights())

        # Send noisy weights to all known peers
        for peer in PEERS:
            send_weights(peer, noisy_weights)

        # Wait before next training round
        time.sleep(ROUND_INTERVAL)
