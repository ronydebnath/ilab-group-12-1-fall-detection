import requests
import random
import json
import time

AGGREGATOR_URL = 'http://localhost:5001'

def generate_dummy_weights(length=3):
    return [round(random.uniform(0.1, 1.0), 3) for _ in range(length)]

def send_weight_update(node_id, weights):
    payload = {
        'node_id': node_id,
        'weights': weights
    }
    try:
        response = requests.post(f"{AGGREGATOR_URL}/submit_weight", json=payload)
        print(f"\n[{node_id}] Response status: {response.status_code}")
        print(f"[{node_id}] Response body: {response.json()}")
    except Exception as e:
        print(f"[{node_id}] Failed to send update: {e}")

def get_global_model():
    try:
        response = requests.get(f"{AGGREGATOR_URL}/get_global_model")
        print("\n[Client] Global model fetch:")
        print(response.json())
    except Exception as e:
        print(f"[Client] Error fetching global model: {e}")

if __name__ == '__main__':
    # Simulate 3 nodes submitting weights
    for node_id in ['external_node1', 'external_node2', 'external_node3']:
        weights = generate_dummy_weights()
        print(f"[{node_id}] Sending weights: {weights}")
        send_weight_update(node_id, weights)
        time.sleep(1)

    # Give the aggregator a second to compute and then fetch global model
    time.sleep(2)
    get_global_model()
