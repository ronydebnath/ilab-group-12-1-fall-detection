# Import necessary libraries
import os  
import time  
import requests  
import numpy
if not numpy.__version__.startswith("1."):
    raise RuntimeError(f"Incompatible NumPy version detected: {numpy.__version__}. Please use numpy<2.")
import tensorflow as tf  
import base64  
import json  

# Get the unique node ID from environment variable or use default
NODE_ID = os.environ.get('NODE_ID', 'node_default')

# Get list of peer addresses from environment variable, split into a list
PEERS = os.environ.get("PEERS", "").split(',')

# Define this node's own address based on NODE_ID
SELF_ADDRESS = f"http://{NODE_ID}:5000"

# Time interval between training rounds (in seconds)
ROUND_INTERVAL = 20

# Load and preprocess the MNIST dataset
(x_train, y_train), (x_test, y_test) = tf.keras.datasets.mnist.load_data()
x_train, x_test = x_train / 255.0, x_test / 255.0  # Normalize pixel values to [0, 1]
x_train = x_train.reshape(-1, 28 * 28)  # Flatten images for input into dense network
x_test = x_test.reshape(-1, 28 * 28)

# Define a simple neural network model with one hidden layer and softmax output
model = tf.keras.Sequential([
    tf.keras.Input(shape=(784,)),
    tf.keras.layers.Dense(64, activation='relu'),
    tf.keras.layers.Dense(10, activation='softmax')
])


# Compile the model with optimizer, loss function, and evaluation metric
model.compile(optimizer='adam', loss='sparse_categorical_crossentropy', metrics=['accuracy'])

# Serialize model weights into a base64-encoded JSON string
def serialize_weights(weights):
    return base64.b64encode(json.dumps([w.tolist() for w in weights]).encode()).decode()

# Deserialize base64-encoded JSON string back into list of NumPy arrays (model weights)
def deserialize_weights(serialized):
    return [np.array(w) for w in json.loads(base64.b64decode(serialized).decode())]

# Train the model on local data for 1 epoch and return the updated weights
def local_training():
    model.fit(x_train, y_train, epochs=1, batch_size=32, verbose=0)
    return model.get_weights()

# Send current node's weights to all peer nodes
def send_weights_to_peers(weights):
    encoded = serialize_weights(weights)

if __name__ == "__main__":
    print(f"{NODE_ID}: Starting training loop...")
    while True:
        weights = local_training()
        send_weights_to_peers(weights)

        print(f"{NODE_ID}: Sent weights to peers.")
        time.sleep(ROUND_INTERVAL)
