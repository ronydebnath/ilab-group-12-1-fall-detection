# node_server.py

# Import required libraries
import zmq
import threading
import time
import os
import base64
import json
import numpy as np

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

def deserialize_weights(serialized):
    """
    Decode base64-encoded weight string and convert it back to a list of NumPy arrays.
    """
    return [np.array(w) for w in json.loads(base64.b64decode(serialized).decode())]

def serialize_weights(weights):
    """
    Convert a list of NumPy arrays to a base64-encoded JSON string for transmission.
    """
    return base64.b64encode(json.dumps([w.tolist() for w in weights]).encode()).decode()

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

    while True:
        try:
            message = socket.recv_json()
            msg_type = message.get("type")

            if msg_type == "send_weight":
                node_id = message.get("node_id")
                weights = deserialize_weights(message.get("weights"))

                with lock:
                    received_weights[node_id] = weights
                    print(f"{NODE_ID}: Received weights from {node_id}")

                socket.send_json({"status": "received"})

            elif msg_type == "get_global_model":
                with lock:
                    payload = {
                        "status": "ok",
                        "model": serialize_weights(global_model) if global_model else None,
                        "version": model_version
                    }
                socket.send_json(payload)

            elif msg_type == "update_global_model":
                new_model = deserialize_weights(message.get("model"))

                with lock:
                    global_model = new_model
                    model_version += 1
                    print(f"{NODE_ID}: Updated global model to version {model_version}")

                socket.send_json({"status": "updated"})

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
