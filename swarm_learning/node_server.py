# node_server.py

# Import required libraries
import zmq
import threading
import time
import os
import base64
import json
import numpy as np
import tensorflow as tf
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Conv1D, MaxPooling1D, LSTM, Dense, Dropout

# Node identifier (can be set via environment variable)
NODE_ID = os.environ.get("NODE_ID", "node_default")

# Port on which ZeroMQ server will listen
ZMQ_PORT = 5555

# Dictionary to store received weights from other nodes
received_weights = {}

# Shared global model (aggregated weights)
global_model = None

# Version counter for the global model
model_version = 0

# Thread lock to safely update shared state (e.g., weights, model version)
lock = threading.Lock()

# ----------------------------- Serialization Helpers -----------------------------

def create_model(input_shape):
    """
    Builds a CNN model for binary fall detection.
    """
    model = tf.keras.Sequential([
        # First Conv1D block
        tf.keras.layers.Conv1D(32, 3, activation='relu', input_shape=input_shape),
        tf.keras.layers.BatchNormalization(),
        tf.keras.layers.MaxPooling1D(2),
        
        # Second Conv1D block
        tf.keras.layers.Conv1D(64, 3, activation='relu'),
        tf.keras.layers.BatchNormalization(),
        tf.keras.layers.MaxPooling1D(2),
        
        # Third Conv1D block
        tf.keras.layers.Conv1D(128, 3, activation='relu'),
        tf.keras.layers.BatchNormalization(),
        tf.keras.layers.MaxPooling1D(2),
        
        # Fourth Conv1D block
        tf.keras.layers.Conv1D(256, 3, activation='relu'),
        tf.keras.layers.BatchNormalization(),
        tf.keras.layers.MaxPooling1D(2),
        
        # Global pooling and dense layers
        tf.keras.layers.GlobalAveragePooling1D(),
        tf.keras.layers.Dense(128, activation='relu'),
        tf.keras.layers.BatchNormalization(),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(1, activation='sigmoid')
    ])
    return model

def deserialize_weights(weights_str):
    """Deserialize base64-encoded weights back into NumPy arrays."""
    weights_list = json.loads(base64.b64decode(weights_str).decode())
    return [np.array(w) for w in weights_list]

def average_weights(weights_list):
    """Average a list of weight arrays."""
    averaged = [np.mean(w, axis=0) for w in zip(*weights_list)]
    print(f"\n{NODE_ID}: Averaged weights statistics:")
    for i, w in enumerate(averaged):
        print(f"  Layer {i}: shape={w.shape}, mean={w.mean():.6f}, std={w.std():.6f}")
    return averaged

# ----------------------------- ZeroMQ Server Logic -----------------------------

def run_zmq_server():
    """
    Main ZeroMQ server loop handling three types of messages:
    1. Receiving model weights from peers.
    2. Providing the current global model to peers.
    3. Updating the global model with new aggregated weights.
    """
    global global_model, model_version

    context = zmq.Context()
    socket = context.socket(zmq.REP)
    socket.bind(f"tcp://0.0.0.0:{ZMQ_PORT}")

    print(f"{NODE_ID}: ZeroMQ server running on port {ZMQ_PORT}")

    # Initialize model with binary classification architecture
    model = create_model((500, 9))  # Using the same input shape as in node.py
    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
        loss='binary_crossentropy',
        metrics=['accuracy']
    )
    
    # Store received weights
    received_weights = []
    unique_peers = set()  # Track unique peers

    while True:
        try:
            message = socket.recv_json()
            msg_type = message.get("type")

            if msg_type == "send_weight":
                # Deserialize and store weights
                sender_id = message.get("node_id", "unknown")
                print(f"\n{NODE_ID}: Received weights from {sender_id}")
                weights = deserialize_weights(message.get("weights"))
                print(f"{NODE_ID}: Received weights statistics:")
                for i, w in enumerate(weights):
                    print(f"  Layer {i}: shape={w.shape}, mean={w.mean():.6f}, std={w.std():.6f}")
                
                received_weights.append(weights)
                unique_peers.add(sender_id)  # Add peer to unique set
                
                # If we have weights from all peers, average them
                peers = os.environ.get('PEERS', '').split(',')
                if peers and len(unique_peers) == len(peers):
                    print(f"\n{NODE_ID}: Received weights from all peers, performing aggregation")
                    averaged_weights = average_weights(received_weights)
                    model.set_weights(averaged_weights)
                    received_weights = []  # Clear the list for next round
                    unique_peers.clear()  # Clear unique peers for next round
                else:
                    print(f"{NODE_ID}: Waiting for more peers ({len(unique_peers)}/{len(peers) if peers else 0})")
                
                socket.send_json({"status": "success"})

            elif msg_type == "get_model":
                # Serialize and send current model weights
                weights = model.get_weights()
                weights_str = base64.b64encode(json.dumps([w.tolist() for w in weights]).encode()).decode()
                socket.send_json({
                    "status": "success",
                    "weights": weights_str
                })

            else:
                print(f"{NODE_ID}: Unknown message type: {msg_type}")
                socket.send_json({"status": "error", "reason": "unknown message type"})

        except Exception as e:
            print(f"{NODE_ID}: Error in server loop: {e}")
            try:
                socket.send_json({"status": "error", "reason": str(e)})
            except zmq.ZMQError:
                pass  # Avoid double-faulting if socket state is inconsistent

# ----------------------------- Start Server Thread -----------------------------

if __name__ == '__main__':
    threading.Thread(target=run_zmq_server, daemon=True).start()
    print(f"{NODE_ID}: Server thread started, continuing with other tasks...")

    # Optional: Keep main thread alive or start training, aggregation, etc.
    while True:
        time.sleep(10)  # Prevent exiting; replace with real logic as needed
