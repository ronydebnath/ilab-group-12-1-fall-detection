 import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ActivityIndicator, KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Alert } from 'react-native';
// Correct the import path to point to AppContext.js
// Correct the hook name to useAppContext
import { useAuth } from '../contexts/AuthContext';
// Import basic shared styles
import { commonStyles as appStyles } from '../styles/commonStyles'; // Using AppStyles as per reverted state
import Ionicons from '@expo/vector-icons/Ionicons'; // For icons

// Create local styles if needed
const localStyles = StyleSheet.create({
  // Add any LoginScreen-specific styles here
  linkText: { // Example of a local style
    marginTop: 15,
    textAlign: 'center',
    color: '#48A6A7', // Basic blue link color
  },
  // Style for user type buttons (from previous version)
  userTypeContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginBottom: 20,
  },
   // Added style for the icon container
  iconContainer: {
    alignItems: 'center',
    marginBottom: 30, // Space below the icon
  },
  // Added style for a welcoming subtitle
  subtitle: {
    fontSize: 18,
    color: '#555', // Medium grey
    textAlign: 'center',
    marginBottom: 30, // Space below the subtitle
  },
});

function LoginScreen({ navigation }) {
  const { signIn, isLoading } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(''); // Local error state

  const handleLogin = async () => {
    setError('');
    if (!email.trim() || !password.trim()) {
      setError('Email and password are required.');
      return;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        setError('Please enter a valid email address.');
        return;
    }

    try {
      await signIn(email, password);
    } catch (e) {
      console.log("LoginScreen: Login attempt failed.");
    }
  };


  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === "ios" ? "padding" : "height"}
      style={appStyles.container} // Apply basic container style
    >
      <ScrollView contentContainerStyle={{ flexGrow: 1, justifyContent: 'center' }}>
        <View style={localStyles.iconContainer}>
            <Ionicons name="medkit-outline" size={64} color="#4CAF50" /> 
        </View>

        <Text style={appStyles.title}>Welcome Back!</Text>
        <Text style={localStyles.subtitle}>Log in to access your fall detection dashboard.</Text>
        {error ? <Text style={{ color: 'red', textAlign: 'center', marginBottom: 10 }}>{error}</Text> : null}

        <TextInput
          style={appStyles.input} // Apply basic input style
          placeholder="Email Address"
          value={email}
          onChangeText={setEmail}
          keyboardType="email-address"
          autoCapitalize="none"
        />
        <TextInput
          style={appStyles.input} // Apply basic input style
          placeholder="Password"
          value={password}
          onChangeText={setPassword}
          secureTextEntry
        />
        <TouchableOpacity
          style={appStyles.button} // Apply basic button style (if defined, otherwise default)
          onPress={handleLogin}
          disabled={isLoading} // Disable while loading
        >
          {isLoading ? (
            // Assign the color using the style defined in commonStyles
            <ActivityIndicator color={appStyles.loginActivityIndicator.color} /> // <-- Assign color from commonStyles
          ) : (
            // Assuming button text color is also in commonStyles.buttonText.color
            <Text style={{ color: '#fff', textAlign: 'center' }}>Login</Text> // Basic white text
          )}
        </TouchableOpacity>
        <TouchableOpacity onPress={() => navigation.navigate('Register')}>
          <Text style={localStyles.linkText}>Don't have an account? Register</Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

export default LoginScreen;