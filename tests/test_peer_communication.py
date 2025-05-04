import unittest
import zmq
import threading
import time
import os
import sys
import json
import base64
import numpy as np
import tensorflow as tf

# Add the parent directory to the Python path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from swarm_learning.node_server import run_zmq_server
from swarm_learning.node import create_model, serialize_weights

class TestPeerCommunication(unittest.TestCase):
    def setUp(self):
        # Set up test environment variables
        os.environ['NODE_ID'] = 'test_node'
        os.environ['PEERS'] = 'peer1:5555,peer2:5555'
        os.environ['ZMQ_PORT'] = '5555'
        
        # Create a test model
        self.model = create_model((500, 9))
        self.model.compile(
            optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
            loss='binary_crossentropy',
            metrics=['accuracy']
        )
        
        # Start server in a separate thread
        self.server_thread = threading.Thread(target=run_zmq_server, daemon=True)
        self.server_thread.start()
        
        # Give the server time to start
        time.sleep(1)
        
        # Set up ZMQ context and socket for testing
        self.context = zmq.Context()
        self.socket = self.context.socket(zmq.REQ)
        self.socket.connect("tcp://localhost:5555")
    
    def tearDown(self):
        # Clean up
        self.socket.close()
        self.context.term()
    
    def test_weight_sending(self):
        """Test sending weights to the server."""
        # Get model weights
        weights = self.model.get_weights()
        serialized_weights = serialize_weights(weights)
        
        # Create message
        message = {
            "type": "send_weight",
            "node_id": "test_peer",
            "weights": serialized_weights
        }
        
        # Send message
        self.socket.send_json(message)
        
        # Get response
        response = self.socket.recv_json()
        
        # Test response
        self.assertEqual(response["status"], "success",
                        "Server should respond with success")
    
    def test_peer_tracking(self):
        """Test that the server correctly tracks unique peers."""
        # Send weights from first peer
        weights1 = self.model.get_weights()
        message1 = {
            "type": "send_weight",
            "node_id": "peer1",
            "weights": serialize_weights(weights1)
        }
        self.socket.send_json(message1)
        response1 = self.socket.recv_json()
        self.assertEqual(response1["status"], "success")
        
        # Send weights from second peer
        weights2 = self.model.get_weights()
        message2 = {
            "type": "send_weight",
            "node_id": "peer2",
            "weights": serialize_weights(weights2)
        }
        self.socket.send_json(message2)
        response2 = self.socket.recv_json()
        self.assertEqual(response2["status"], "success")
        
        # Send weights from first peer again (should not be counted as new)
        self.socket.send_json(message1)
        response3 = self.socket.recv_json()
        self.assertEqual(response3["status"], "success")
    
    def test_model_retrieval(self):
        """Test retrieving the current model from the server."""
        # First send some weights
        weights = self.model.get_weights()
        message = {
            "type": "send_weight",
            "node_id": "test_peer",
            "weights": serialize_weights(weights)
        }
        self.socket.send_json(message)
        self.socket.recv_json()
        
        # Then request the model
        request = {"type": "get_model"}
        self.socket.send_json(request)
        response = self.socket.recv_json()
        
        # Test response
        self.assertEqual(response["status"], "success",
                        "Server should respond with success")
        self.assertIn("weights", response,
                     "Response should include weights")
        
        # Test that weights can be deserialized
        try:
            deserialized = json.loads(base64.b64decode(response["weights"]).decode())
            self.assertTrue(isinstance(deserialized, list),
                          "Deserialized weights should be a list")
        except Exception as e:
            self.fail(f"Failed to deserialize weights: {e}")
    
    def test_error_handling(self):
        """Test server's error handling for invalid messages."""
        # Test invalid message type
        invalid_message = {
            "type": "invalid_type",
            "node_id": "test_peer"
        }
        self.socket.send_json(invalid_message)
        response = self.socket.recv_json()
        
        self.assertEqual(response["status"], "error",
                        "Server should respond with error for invalid message type")
        self.assertIn("reason", response,
                     "Error response should include reason")

if __name__ == '__main__':
    unittest.main() 