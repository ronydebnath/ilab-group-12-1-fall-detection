# node.py
import os
import time
import requests
import numpy as np

# Retrieve environment variables
AGGREGATOR_URL = os.environ.get('AGGREGATOR_URL', 'http://localhost:5000')
NODE_ID = os.environ.get('NODE_ID', 'node_default')

def local_training():
    # Simulate local training: generate dummy weight vector
    dummy_weights = np.random.rand(3).tolist()
    return dummy_weights

def send_weight_update(weights):
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
    while True:
        local_weights = local_training()
        print(f"Node {NODE_ID}: Trained local model weights: {local_weights}")
        updated_global_model = send_weight_update(local_weights)
        # In practice, update your local model with the global model here.
        time.sleep(10)  # Wait before next round

if __name__ == '__main__':
    main()
