# node.py
import os
import time
import requests
import numpy as np

# Retrieve environment variables; defaults are provided for testing locally.
AGGREGATOR_URL = os.environ.get('AGGREGATOR_URL', 'http://localhost:5000')
NODE_ID = os.environ.get('NODE_ID', 'node_default')

def local_training():
    """
    Simulate local training by generating a dummy weight vector.
    Replace this with your actual training code.
    For example, a vector of 3 random weights.
    """
    dummy_weights = np.random.rand(3).tolist()
    return dummy_weights

def send_weight_update(weights):
    """
    Sends the local model weights to the aggregator via a REST API.
    Returns the global model from the aggregator if available.
    """
    payload = {
        'node_id': NODE_ID,
        'weights': weights
    }
    try:
        response = requests.post(f"{AGGREGATOR_URL}/submit_weight", json=payload)
        if response.status_code == 200:
            data = response.json()
            if 'global_model' in data:
                print(f"Node {NODE_ID}: Received updated global model: {data['global_model']}")
                return data['global_model']
            else:
                print(f"Node {NODE_ID}: Update acknowledged: {data.get('message', '')}")
        else:
            print(f"Node {NODE_ID}: Failed to send update: {response.text}")
    except Exception as e:
        print(f"Node {NODE_ID}: Error sending weight update: {e}")
    return None

def main():
    """
    Main loop: simulate local training, send weight update, and wait before the next round.
    """
    while True:
        # Simulate local training
        local_weights = local_training()
        print(f"Node {NODE_ID}: Trained local model weights: {local_weights}")
        
        # Send the weight update to the aggregator and try to get the updated global model
        updated_global_model = send_weight_update(local_weights)
        
        # In a real implementation, you'd update your local model with the global model weights.
        # For simulation, we'll just print them.
        
        # Wait for a fixed interval before next training round (e.g., 10 seconds)
        time.sleep(10)

if __name__ == '__main__':
    main()
