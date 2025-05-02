# swarm_learning/node.py

# Import required libraries
import os
import time
import numpy as np
if not np.__version__.startswith("1."):
    raise RuntimeError(f"Incompatible NumPy version detected: {np.__version__}. Please use numpy<2.")

import tensorflow as tf
# Limit TensorFlow CPU parallelism to avoid excessive memory usage
tf.config.threading.set_intra_op_parallelism_threads(1)
tf.config.threading.set_inter_op_parallelism_threads(1)

import base64
import json
import zmq
import pandas as pd
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split
from sklearn.utils import class_weight

# Configuration from environment variables
NODE_ID     = os.environ.get('NODE_ID', 'node_default')
DATA_PATH   = os.environ.get('DATA_PATH', '/app/data/df_filtered_binary.pkl')
peers_env   = os.environ.get('PEERS', '')
PEERS       = [p.strip() for p in peers_env.split(',') if p.strip()] if peers_env else []
ROUND_INTERVAL = 20        # seconds between training rounds
ZMQ_PORT       = 5555      # ZeroMQ communication port

# ----------------------------- Data Loading and Preprocessing -----------------------------

def create_windows(X, y, window_size=400, step_size=62, positive_label="FALL"):
    """
    Creates overlapping windows from the sensor data with any-positive labeling.
    """
    X_windows, y_windows = [], []
    
    for start in range(0, len(X) - window_size + 1, step_size):
        end = start + window_size
        window_data = X[start:end]
        window_labels = y[start:end]
        
        # any-positive labeling
        if np.any(window_labels == positive_label):
            label = positive_label
        else:
            label = "ADL"  # default class

        X_windows.append(window_data)
        y_windows.append(label)

    return np.array(X_windows), np.array(y_windows)

def load_and_preprocess_data():
    """
    Loads the pickled DataFrame from DATA_PATH, creates overlapping windows,
    encodes labels, and splits into train/test sets.
    """
    # 1. Load DataFrame
    print(f"{NODE_ID}: Loading data from {DATA_PATH}")
    if not os.path.isfile(DATA_PATH):
        raise FileNotFoundError(f"{NODE_ID}: Data file not found at {DATA_PATH}")
    df = pd.read_pickle(DATA_PATH)
    print(f"{NODE_ID}: Successfully loaded data ({len(df)} rows)")

    # 2. Define sensor columns
    sensor_cols = ['acc_x', 'acc_y', 'acc_z', 'gyro_x', 'gyro_y', 'gyro_z', 'azimuth', 'pitch', 'roll']

    # 3. Split data into train/test sets
    X_train, X_test, y_train, y_test = train_test_split(
        df[sensor_cols], df['fall_label'], test_size=0.15, random_state=42
    )

    # 4. Create windows
    X_train_windows, y_train_windows = create_windows(X_train, y_train)
    X_test_windows, y_test_windows = create_windows(X_test, y_test)

    # 5. Encode labels
    label_map = {'ADL': 0, 'FALL': 1}
    y_train_encoded = np.vectorize(label_map.get)(y_train_windows)
    y_test_encoded = np.vectorize(label_map.get)(y_test_windows)

    print(f"{NODE_ID}: Data preprocessing completed. Train shape: {X_train_windows.shape}")

    return X_train_windows, X_test_windows, y_train_encoded, y_test_encoded

# Load data once at startup
X_train, X_test, y_train, y_test = load_and_preprocess_data()

# ----------------------------- Model Definition -----------------------------

def create_model(input_shape):
    """
    Builds a CNN model for binary fall detection.
    """
    model = tf.keras.Sequential([
        tf.keras.layers.Conv1D(filters=64, kernel_size=5, activation='relu', input_shape=input_shape),
        tf.keras.layers.Conv1D(filters=64, kernel_size=5, activation='relu'),
        tf.keras.layers.Conv1D(filters=64, kernel_size=5, activation='relu'),
        tf.keras.layers.Conv1D(filters=64, kernel_size=5, activation='relu'),
        tf.keras.layers.Flatten(),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(128, activation='relu'),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(128, activation='relu'),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(1, activation='sigmoid')  # Binary output
    ])
    return model

# Compute class weights
class_weights = class_weight.compute_class_weight(
    class_weight='balanced',
    classes=np.unique(y_train),
    y=y_train
)
class_weights = dict(enumerate(class_weights))

# Instantiate and compile model
model = create_model((X_train.shape[1], X_train.shape[2]))
model.compile(
    optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
    loss='binary_crossentropy',
    metrics=[tf.keras.metrics.Precision(), tf.keras.metrics.Recall()]
)

# ----------------------------- Utility Functions -----------------------------

def serialize_weights(weights):
    """
    Serialize model weights (list of NumPy arrays) to a base64-encoded JSON string.
    """
    return base64.b64encode(
        json.dumps([w.tolist() for w in weights]).encode()
    ).decode()

def add_noise(weights, noise_std=0.01):
    """
    Adds Gaussian noise for differential privacy to a list of weight arrays.
    """
    return [w + np.random.normal(0, noise_std, size=w.shape) for w in weights]

def send_weights(peer, weights):
    """
    Sends the (noisy) weights to a peer via ZeroMQ REQ/REP with a 3s timeout.
    """
    context = zmq.Context()
    socket  = context.socket(zmq.REQ)
    socket.RCVTIMEO = 3000
    try:
        print(f"\n{NODE_ID}: Connecting to {peer}")
        print(f"{NODE_ID}: Sending weights with shapes:")
        for i, w in enumerate(weights):
            print(f"  Layer {i}: shape={w.shape}, mean={w.mean():.6f}, std={w.std():.6f}")
        
        socket.connect(f"tcp://{peer}")
        payload = {
            "type": "send_weight",
            "node_id": NODE_ID,
            "weights": serialize_weights(weights)
        }
        socket.send_json(payload)
        reply = socket.recv_json()
        print(f"{NODE_ID}: Sent weights to {peer}, status: {reply.get('status')}")
    except zmq.error.Again:
        print(f"{NODE_ID}: Timeout when sending to {peer}")
    except Exception as e:
        print(f"{NODE_ID}: Error sending to {peer}: {e}")
    finally:
        socket.close()
        context.term()

# ----------------------------- Main Training Loop -----------------------------

if __name__ == "__main__":
    print(f"{NODE_ID}: Starting training loop with peers: {PEERS}")
    while True:
        # 1. Local training
        print(f"{NODE_ID}: Before training")
        model.fit(X_train, y_train, epochs=1, batch_size=64, class_weight=class_weights, verbose=0)
        print(f"{NODE_ID}: After training")

        # 2. Prepare and send noisy weights
        noisy = add_noise(model.get_weights())
        for peer in PEERS:
            send_weights(peer, noisy)

        # 3. Wait for next round
        time.sleep(ROUND_INTERVAL)
