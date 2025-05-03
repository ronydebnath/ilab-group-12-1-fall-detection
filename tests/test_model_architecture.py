import unittest
import tensorflow as tf
import numpy as np
import sys
import os

# Add the parent directory to the Python path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from swarm_learning.node import create_model as node_create_model
from swarm_learning.node_server import create_model as server_create_model

class TestModelArchitecture(unittest.TestCase):
    def setUp(self):
        self.input_shape = (500, 9)  # Standard input shape for our data
        
    def test_model_architecture_consistency(self):
        """Test that both node.py and node_server.py create identical model architectures."""
        # Create models from both files
        node_model = node_create_model(self.input_shape)
        server_model = server_create_model(self.input_shape)
        
        # Test number of layers
        self.assertEqual(len(node_model.layers), len(server_model.layers),
                        "Number of layers should be identical")
        
        # Test layer types and configurations
        for node_layer, server_layer in zip(node_model.layers, server_model.layers):
            self.assertEqual(type(node_layer), type(server_layer),
                           f"Layer type mismatch: {type(node_layer)} vs {type(server_layer)}")
            
            # Test layer configurations
            if isinstance(node_layer, tf.keras.layers.Conv1D):
                self.assertEqual(node_layer.filters, server_layer.filters,
                               "Conv1D filters should match")
                self.assertEqual(node_layer.kernel_size, server_layer.kernel_size,
                               "Conv1D kernel size should match")
                self.assertEqual(node_layer.activation.__name__, server_layer.activation.__name__,
                               "Conv1D activation should match")
            
            elif isinstance(node_layer, tf.keras.layers.Dense):
                self.assertEqual(node_layer.units, server_layer.units,
                               "Dense layer units should match")
                self.assertEqual(node_layer.activation.__name__, server_layer.activation.__name__,
                               "Dense layer activation should match")
    
    def test_model_output_shape(self):
        """Test that both models produce the expected output shape."""
        node_model = node_create_model(self.input_shape)
        server_model = server_create_model(self.input_shape)
        
        # Create dummy input
        dummy_input = np.random.random((1,) + self.input_shape)
        
        # Get predictions
        node_output = node_model.predict(dummy_input)
        server_output = server_model.predict(dummy_input)
        
        # Test output shapes
        self.assertEqual(node_output.shape, server_output.shape,
                        "Output shapes should match")
        self.assertEqual(node_output.shape[-1], 1,
                        "Output should be binary (1 unit)")
    
    def test_model_compilation(self):
        """Test that both models compile with the same optimizer and loss."""
        node_model = node_create_model(self.input_shape)
        server_model = server_create_model(self.input_shape)
        
        # Compile models
        node_model.compile(
            optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
            loss='binary_crossentropy',
            metrics=['accuracy']
        )
        
        server_model.compile(
            optimizer=tf.keras.optimizers.Adam(learning_rate=0.001),
            loss='binary_crossentropy',
            metrics=['accuracy']
        )
        
        # Test optimizer
        self.assertEqual(type(node_model.optimizer), type(server_model.optimizer),
                        "Optimizer types should match")
        
        # Test loss function
        self.assertEqual(node_model.loss, server_model.loss,
                        "Loss functions should match")

if __name__ == '__main__':
    unittest.main() 