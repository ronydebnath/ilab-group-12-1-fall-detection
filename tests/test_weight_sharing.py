import unittest
import tensorflow as tf
import numpy as np
import sys
import os
import base64
import json

# Add the parent directory to the Python path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from swarm_learning.node import serialize_weights, add_noise
from swarm_learning.node_server import deserialize_weights, average_weights

class TestWeightSharing(unittest.TestCase):
    def setUp(self):
        # Create a simple model for testing
        self.model = tf.keras.Sequential([
            tf.keras.layers.Conv1D(64, 3, activation='relu', input_shape=(500, 9)),
            tf.keras.layers.MaxPooling1D(2),
            tf.keras.layers.Dense(1, activation='sigmoid')
        ])
        self.model.compile(optimizer='adam', loss='binary_crossentropy')
        
    def test_weight_serialization_deserialization(self):
        """Test that weights can be serialized and deserialized correctly."""
        # Get model weights
        original_weights = self.model.get_weights()
        
        # Serialize weights
        serialized = serialize_weights(original_weights)
        
        # Deserialize weights
        deserialized = deserialize_weights(serialized)
        
        # Test that weights are preserved
        self.assertEqual(len(original_weights), len(deserialized),
                        "Number of weight arrays should be preserved")
        
        for orig, deser in zip(original_weights, deserialized):
            self.assertEqual(orig.shape, deser.shape,
                           "Weight shapes should be preserved")
            np.testing.assert_array_almost_equal(orig, deser,
                                               "Weight values should be preserved")
    
    def test_weight_noise_addition(self):
        """Test that noise is added correctly to weights."""
        original_weights = self.model.get_weights()
        noisy_weights = add_noise(original_weights, noise_std=0.01)
        
        # Test that shapes are preserved
        self.assertEqual(len(original_weights), len(noisy_weights),
                        "Number of weight arrays should be preserved")
        
        for orig, noisy in zip(original_weights, noisy_weights):
            self.assertEqual(orig.shape, noisy.shape,
                           "Weight shapes should be preserved")
            
            # Test that values are different (noise was added)
            self.assertFalse(np.array_equal(orig, noisy),
                           "Noisy weights should be different from original")
            
            # Test that the difference is reasonable (within 3 standard deviations)
            diff = np.abs(noisy - orig)
            self.assertTrue(np.all(diff < 0.03),  # 3 * noise_std
                          "Noise should be within reasonable bounds")
    
    def test_weight_averaging(self):
        """Test that weight averaging works correctly."""
        # Create multiple sets of weights
        weight_sets = []
        for _ in range(3):
            weights = self.model.get_weights()
            weight_sets.append(weights)
        
        # Average the weights
        averaged = average_weights(weight_sets)
        
        # Test that shapes are preserved
        self.assertEqual(len(weight_sets[0]), len(averaged),
                        "Number of weight arrays should be preserved")
        
        # Test that averaging worked correctly
        for i in range(len(averaged)):
            expected = np.mean([w[i] for w in weight_sets], axis=0)
            np.testing.assert_array_almost_equal(averaged[i], expected,
                                               "Averaged weights should match expected values")
    
    def test_end_to_end_weight_sharing(self):
        """Test the complete weight sharing process."""
        # Get original weights
        original_weights = self.model.get_weights()
        
        # Add noise
        noisy_weights = add_noise(original_weights)
        
        # Serialize
        serialized = serialize_weights(noisy_weights)
        
        # Deserialize
        deserialized = deserialize_weights(serialized)
        
        # Create multiple sets of deserialized weights
        weight_sets = [deserialized for _ in range(3)]
        
        # Average
        averaged = average_weights(weight_sets)
        
        # Test that shapes are preserved throughout
        self.assertEqual(len(original_weights), len(averaged),
                        "Number of weight arrays should be preserved through the entire process")
        
        for orig, avg in zip(original_weights, averaged):
            self.assertEqual(orig.shape, avg.shape,
                           "Weight shapes should be preserved through the entire process")

if __name__ == '__main__':
    unittest.main() 