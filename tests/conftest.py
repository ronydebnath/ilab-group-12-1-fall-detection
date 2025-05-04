import pytest
import tensorflow as tf
import numpy as np
import os

@pytest.fixture
def model():
    """Create a test model with the standard architecture."""
    model = tf.keras.Sequential([
        tf.keras.layers.Conv1D(64, 3, activation='relu', input_shape=(500, 9)),
        tf.keras.layers.MaxPooling1D(2),
        tf.keras.layers.Conv1D(64, 3, activation='relu'),
        tf.keras.layers.MaxPooling1D(2),
        tf.keras.layers.Conv1D(64, 3),
        tf.keras.layers.GlobalAveragePooling1D(),
        tf.keras.layers.BatchNormalization(),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(1, activation='sigmoid')
    ])
    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
        loss='binary_crossentropy',
        metrics=['accuracy']
    )
    return model

@pytest.fixture
def test_data():
    """Create test data with the correct shape."""
    X = np.random.random((100, 500, 9))  # 100 samples, 500 timesteps, 9 features
    y = np.random.randint(0, 2, (100,))  # Binary labels
    return X, y

@pytest.fixture
def test_environment():
    """Set up test environment variables."""
    os.environ['NODE_ID'] = 'test_node'
    os.environ['PEERS'] = 'peer1:5555,peer2:5555'
    os.environ['ZMQ_PORT'] = '5555'
    os.environ['DATA_PATH'] = '/app/data/test_data.csv'
    return os.environ

@pytest.fixture
def zmq_context():
    """Create a ZMQ context for testing."""
    import zmq
    context = zmq.Context()
    yield context
    context.term()

@pytest.fixture
def zmq_socket(zmq_context):
    """Create a ZMQ socket for testing."""
    socket = zmq_context.socket(zmq.REQ)
    socket.connect("tcp://localhost:5555")
    yield socket
    socket.close() 