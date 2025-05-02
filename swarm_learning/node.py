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
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.model_selection import train_test_split, GroupShuffleSplit
from sklearn.utils import class_weight

# Configuration from environment variables
NODE_ID     = os.environ.get('NODE_ID', 'node_default')
DATA_PATH   = os.environ.get('DATA_PATH', '/app/data/MobiAct_combined.csv')
peers_env   = os.environ.get('PEERS', '')
PEERS       = [p.strip() for p in peers_env.split(',') if p.strip()] if peers_env else []
ROUND_INTERVAL = 20        # seconds between training rounds
ZMQ_PORT       = 5555      # ZeroMQ communication port

# ----------------------------- Data Loading and Preprocessing -----------------------------

def create_windows(X, y, window_size=500, step_size=250):
    """
    Create windows for binary classification. Skip windows with both ADL and FALL.
    """
    X_windows, y_windows = [], []
    for start in range(0, len(X) - window_size + 1, step_size):
        end = start + window_size
        window_data = X[start:end]
        window_labels = y[start:end]
        unique_labels = set(window_labels)
        # Labeling logic: skip mixed ADL/FALL, label as FALL if any FALL present
        if "ADL" in unique_labels and "FALL" in unique_labels:
            continue
        elif "FALL" in unique_labels:
            label = "FALL"
        elif "ADL" in unique_labels:
            label = "ADL"
        else:
            continue
        X_windows.append(window_data)
        y_windows.append(label)
    return np.array(X_windows), np.array(y_windows)

def load_and_preprocess_data():
    print(f"{NODE_ID}: Loading data from {DATA_PATH}")
    if not os.path.isfile(DATA_PATH):
        raise FileNotFoundError(f"{NODE_ID}: Data file not found at {DATA_PATH}")
    df = pd.read_csv(DATA_PATH)
    print(f"{NODE_ID}: Successfully loaded data ({len(df)} rows)")
    sensor_cols = ['acc_x', 'acc_y', 'acc_z', 'gyro_x', 'gyro_y', 'gyro_z', 'azimuth', 'pitch', 'roll']
    # Standardize all 9 sensor columns
    scaler = StandardScaler()
    df[sensor_cols] = scaler.fit_transform(df[sensor_cols])
    # Split by subject for better generalization (optional, but notebook does this)
    groups = df['subject_id'].values
    gss1 = GroupShuffleSplit(n_splits=1, test_size=0.2, random_state=42)
    trainval_idx, test_idx = next(gss1.split(df, df['fall_label'], groups=groups))
    trainval_subjects = df.iloc[trainval_idx]['subject_id'].unique()
    test_subjects = df.iloc[test_idx]['subject_id'].unique()
    df_trainval = df[df['subject_id'].isin(trainval_subjects)].copy()
    df_test     = df[df['subject_id'].isin(test_subjects)].copy()
    # 2nd split for val
    groups_trainval = df_trainval['subject_id'].values
    gss2 = GroupShuffleSplit(n_splits=1, test_size=0.125, random_state=42)
    train_idx, val_idx = next(gss2.split(df_trainval, df_trainval['fall_label'], groups=groups_trainval))
    train_subjects = df_trainval.iloc[train_idx]['subject_id'].unique()
    val_subjects   = df_trainval.iloc[val_idx]['subject_id'].unique()
    df_train = df_trainval[df_trainval['subject_id'].isin(train_subjects)].copy()
    df_val   = df_trainval[df_trainval['subject_id'].isin(val_subjects)].copy()
    # Windowing
    X_train, y_train = create_windows(df_train[sensor_cols].values, df_train['fall_label'].values)
    X_val, y_val     = create_windows(df_val[sensor_cols].values, df_val['fall_label'].values)
    X_test, y_test   = create_windows(df_test[sensor_cols].values, df_test['fall_label'].values)
    # Encode labels
    label_map = {'ADL': 0, 'FALL': 1}
    y_train_encoded = np.vectorize(label_map.get)(y_train)
    y_val_encoded   = np.vectorize(label_map.get)(y_val)
    y_test_encoded  = np.vectorize(label_map.get)(y_test)
    print(f"{NODE_ID}: Data preprocessing completed. Train shape: {X_train.shape}")
    return X_train, X_val, X_test, y_train_encoded, y_val_encoded, y_test_encoded

# Load data once at startup
X_train, X_val, X_test, y_train, y_val, y_test = load_and_preprocess_data()

# ----------------------------- Model Definition -----------------------------

def create_model(input_shape):
    """
    Builds a CNN model for binary fall detection.
    """
    model = tf.keras.Sequential([
        tf.keras.layers.Conv1D(64, 3, activation='relu', input_shape=input_shape),
        tf.keras.layers.MaxPooling1D(2),
        tf.keras.layers.Conv1D(64, 3, activation='relu'),
        tf.keras.layers.MaxPooling1D(2),
        tf.keras.layers.Conv1D(64, 3),
        tf.keras.layers.GlobalAveragePooling1D(),
        tf.keras.layers.BatchNormalization(),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(1, activation='sigmoid')
    ])
    return model

# Compute class weights
y_train_array = np.array(y_train)
class_weights = class_weight.compute_class_weight(
    class_weight='balanced',
    classes=np.unique(y_train_array),
    y=y_train_array
)
class_weights = dict(enumerate(class_weights))

# Instantiate and compile model
model = create_model((X_train.shape[1], X_train.shape[2]))
model.compile(
    optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
    loss='binary_crossentropy',
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
        print(f"{NODE_ID}: Before training")
        model.fit(X_train, y_train, epochs=1, batch_size=64, class_weight=class_weights, verbose=0, validation_data=(X_val, y_val))
        print(f"{NODE_ID}: After training")
        noisy = add_noise(model.get_weights())
        for peer in PEERS:
            send_weights(peer, noisy)
        time.sleep(ROUND_INTERVAL)
