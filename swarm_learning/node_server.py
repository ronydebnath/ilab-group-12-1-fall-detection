# Import necessary modules
from flask import Flask, request, jsonify  
import numpy as np  
import threading  
import os  
import requests  
import base64  
import json  

# Initialize the Flask app
app = Flask(__name__)

# Dictionary to store received weights from other nodes
received_weights = {}

# Global variable to store the most recent global model weights
global_model = None

# Lock to ensure thread-safe operations when accessing/modifying shared data
lock = threading.Lock()

# Node ID of this server instance (default to 'node_default' if not set)
NODE_ID = os.environ.get("NODE_ID", "node_default")

# List of peer node addresses (e.g., "http://node1:5000,http://node2:5000")
PEERS = os.environ.get("PEERS", "").split(",")

# Endpoint to receive weights from peers (or self)
@app.route('/receive_weight', methods=['POST'])
def receive_weight():
    data = request.get_json()  # Parse incoming JSON data
    node_id = data.get('node_id')  # Extract sender's node ID
    weights = json.loads(base64.b64decode(data.get('weights')).decode())  # Decode and deserialize weights

    # Validate input data
    if not node_id or not weights:
        return jsonify({'error': 'Missing data'}), 400

    # Thread-safe update to the received_weights dictionary
    with lock:
        received_weights[node_id] = weights

    print(f"{NODE_ID}: Received weights from {node_id}")
    return jsonify({'status': 'received'})  # Return success status

# Endpoint to update the local copy of the global model with new aggregated weights
@app.route('/update_model', methods=['POST'])
def update_model():
    global global_model  # Use global variable to store the updated model
    data = request.get_json()  # Parse incoming JSON data

    # Decode and deserialize the received model
    global_model = json.loads(base64.b64decode(data.get('model')).decode())
    print(f"{NODE_ID}: Received new global model")

    return jsonify({'status': 'updated'})  # Acknowledge update

# Endpoint to retrieve the current global model weights (used by leader for aggregation)
@app.route('/get_model', methods=['GET'])
def get_model():
    return jsonify({'global_model': global_model})  # Return global model weights as JSON

@app.route('/evaluate', methods=['GET'])
def evaluate_model():
    from swarm_learning.node import model, x_test, y_test
    loss, acc = model.evaluate(x_test, y_test, verbose=0)
    return jsonify({
        'loss': float(loss),
        'accuracy': float(acc)
    })

# Start the Flask server when script is run directly
if __name__ == '__main__':
    # Bind to all available IP addresses so that other containers/hosts can connect
    app.run(host='0.0.0.0', port=5000)
