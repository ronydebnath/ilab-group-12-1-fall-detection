# aggregator.py
from flask import Flask, request, jsonify
import numpy as np
import threading

app = Flask(__name__)

# Global list to store received weight updates
received_weights = []
lock = threading.Lock()

# Set expected number of nodes to trigger aggregation
EXPECTED_UPDATES = 3

# Global aggregated model (dummy initialisation)
global_model = None

def aggregate_weights(weights_list):
    """Aggregate (average) a list of weight vectors."""
    weights_array = np.array(weights_list)
    return weights_array.mean(axis=0).tolist()

@app.route('/submit_weight', methods=['POST'])
def submit_weight():
    global global_model
    data = request.get_json()
    if 'node_id' not in data or 'weights' not in data:
        return jsonify({'error': 'Missing node_id or weights'}), 400
    
    weight_update = data['weights']
    
    with lock:
        received_weights.append(weight_update)
        print(f"Aggregator: Received update from {data['node_id']}: {weight_update}")
        if len(received_weights) >= EXPECTED_UPDATES:
            # Aggregate the weights
            global_model = aggregate_weights(received_weights)
            print(f"Aggregator: New global model aggregated: {global_model}")
            # Clear updates list for next round
            received_weights.clear()
    
    # Return global model if available, else indicate waiting for more updates
    if global_model is not None:
        return jsonify({'global_model': global_model}), 200
    else:
        return jsonify({'message': 'Update received, waiting for more nodes...'}), 200

@app.route('/get_global_model', methods=['GET'])
def get_global_model():
    if global_model is not None:
        return jsonify({'global_model': global_model}), 200
    else:
        return jsonify({'message': 'Global model not yet available'}), 200

if __name__ == '__main__':
    # Run the server on 0.0.0.0:5000 so it is accessible by containers
    app.run(host='0.0.0.0', port=5000)
