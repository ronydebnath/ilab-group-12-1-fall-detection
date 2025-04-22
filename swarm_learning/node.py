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

# Configuration from environment variables
NODE_ID     = os.environ.get('NODE_ID', 'node_default')
DATA_PATH   = os.environ.get('DATA_PATH', '/app/data/df_filtered_cnn.pkl')
peers_env   = os.environ.get('PEERS', '')
PEERS       = [p.strip() for p in peers_env.split(',') if p.strip()] if peers_env else []
ROUND_INTERVAL = 20        # seconds between training rounds
ZMQ_PORT       = 5555      # ZeroMQ communication port

# ----------------------------- Data Loading and Preprocessing -----------------------------

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
    sensor_cols = ['acc_x', 'acc_y', 'acc_z', 'gyro_x', 'gyro_y', 'gyro_z']

    # 3. Create sliding windows with majority-voted labels
    def create_windows(data, window_size=25, step_size=12):
        X, y = [], []
        arr   = data[sensor_cols].values
        labs  = data['label'].values
        for start in range(0, len(data) - window_size + 1, step_size):
            end = start + window_size
            window_data   = arr[start:end]
            window_labels = labs[start:end]
            # majority vote
            uniq, cnts = np.unique(window_labels, return_counts=True)
            label = uniq[np.argmax(cnts)]
            X.append(window_data)
            y.append(label)
        return np.array(X), np.array(y)

    X, y = create_windows(df)
    le    = LabelEncoder()
    y_enc = le.fit_transform(y)

    # 4. Split into train/test
    X_train, X_test, y_train, y_test = train_test_split(
        X, y_enc, test_size=0.15, random_state=42
    )
    print(f"{NODE_ID}: Data preprocessing completed. Train shape: {X_train.shape}")

    return X_train, X_test, y_train, y_test, len(np.unique(y_enc))

# Load data once at startup
X_train, X_test, y_train, y_test, num_classes = load_and_preprocess_data()

# ----------------------------- Model Definition -----------------------------

def create_model(input_shape, num_classes):
    """
    Builds a CNN-LSTM model for fall detection.
    """
    model = tf.keras.Sequential([
        tf.keras.layers.Conv1D(64, 3, activation='relu', input_shape=input_shape),
        tf.keras.layers.Conv1D(128, 3, activation='relu'),
        tf.keras.layers.MaxPooling1D(2),
        tf.keras.layers.Conv1D(128, 3, activation='relu'),
        tf.keras.layers.LSTM(64),
        tf.keras.layers.Dense(64, activation='relu'),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(num_classes, activation='softmax')
    ])
    return model

# Instantiate and compile model
model = create_model((X_train.shape[1], X_train.shape[2]), num_classes)
model.compile(
    optimizer='adam',
    loss='sparse_categorical_crossentropy',
    metrics=['accuracy']
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
        print(f"{NODE_ID}: Connecting to {peer}")
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
        model.fit(X_train, y_train, epochs=1, batch_size=32, verbose=0)
        print(f"{NODE_ID}: After training")

        # 2. Prepare and send noisy weights
        noisy = add_noise(model.get_weights())
        for peer in PEERS:
            send_weights(peer, noisy)

        # 3. Wait for next round
        time.sleep(ROUND_INTERVAL)
