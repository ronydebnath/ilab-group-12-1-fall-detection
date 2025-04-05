import os
import time
import threading
import requests
import numpy as np
from flask import Flask, request, jsonify

app = Flask(__name__)

NODE_ID = os.environ.get('NODE_ID', 'node_default')
PEERS = [peer for peer in os.environ.get('PEERS', '').split(',') if peer]
ROUND_DURATION = 20  # seconds

received_weights = {}
global_model = None
lock = threading.Lock()

def local_training():
    # Simulate local training: generate dummy weights
    return np.random.rand(3).tolist()

def elect_leader(peers):
    all_nodes = peers + [NODE_ID]
    sorted_nodes = sorted(all_nodes)
    return sorted_nodes[0]

def share_weights_with_peers(weights):
    for peer in PEERS:
        try:
            requests.post(f"{peer}/receive_weight", json={
                'node_id': NODE_ID,
                'weights': weights
            }, timeout=3)
        except Exception as e:
            print(f"[{NODE_ID}] Failed to send to {peer}: {e}")

def broadcast_global_model(model):
    for peer in PEERS:
        try:
            requests.post(f"{peer}/receive_global_model", json={
                'global_model': model
            }, timeout=3)
        except Exception as e:
            print(f"[{NODE_ID}] Failed to broadcast to {peer}: {e}")

@app.route('/receive_weight', methods=['POST'])
def receive_weight():
    data = request.get_json()
    node = data['node_id']
    weights = data['weights']
    with lock:
        received_weights[node] = weights
    print(f"[{NODE_ID}] Received weights from {node}: {weights}")
    return jsonify({'status': 'ok'})

@app.route('/receive_global_model', methods=['POST'])
def receive_global_model():
    global global_model
    data = request.get_json()
    with lock:
        global_model = data['global_model']
    print(f"[{NODE_ID}] Updated global model: {global_model}")
    return jsonify({'status': 'global_model_updated'})

def aggregate_weights():
    with lock:
        if not received_weights:
            return None
        weights = np.array(list(received_weights.values()))
        return weights.mean(axis=0).tolist()

def run_federated_loop():
    global global_model
    round_num = 0
    while True:
        print(f"\n[{NODE_ID}] --- Federated Round {round_num} ---")

        # Local training
        local_weights = local_training()
        print(f"[{NODE_ID}] Local weights: {local_weights}")

        # Share with peers
        share_weights_with_peers(local_weights)

        # Add own weights
        with lock:
            received_weights[NODE_ID] = local_weights

        time.sleep(ROUND_DURATION // 2)  # Wait for others to send

        leader = elect_leader(PEERS)
        print(f"[{NODE_ID}] Leader elected: {leader}")

        # If leader, aggregate and broadcast
        if f"http://{NODE_ID}:5000" == leader:
            model = aggregate_weights()
            if model:
                print(f"[{NODE_ID}] Aggregated global model: {model}")
                with lock:
                    global_model = model
                broadcast_global_model(model)

        # Reset for next round
        with lock:
            received_weights.clear()

        time.sleep(ROUND_DURATION // 2)
        round_num += 1

if __name__ == '__main__':
    threading.Thread(target=run_federated_loop, daemon=True).start()
    app.run(host='0.0.0.0', port=5000)
