import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ActivityIndicator, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { useAuth } from '../contexts/AuthContext';
import { commonStyles } from '../styles/commonStyles';
import Ionicons from '@expo/vector-icons/Ionicons';
import * as Device from 'expo-device';

export default function RegisterScreen({ navigation }) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState(''); // Local error state for form validation
  const { signUp, isLoading } = useAuth();

  const handleRegister = async () => {
    setError(''); // Clear previous errors
    if (!name.trim() || !email.trim() || !password.trim() || !passwordConfirmation.trim()) {
      setError('All fields are required.');
      return;
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        setError('Please enter a valid email address.');
        return;
    }
    if (password.length < 6) { // Example: Basic password length validation
        setError('Password must be at least 6 characters long.');
        return;
    }
    if (password !== passwordConfirmation) {
      setError('Passwords do not match.');
      return;
    }

    try {
      await signUp(name, email, password, passwordConfirmation);
      // Alert is shown in AuthContext's signUp.
      // Navigate to Login screen after successful registration alert.
      navigation.navigate('Login'); 
    } catch (e) {
      // Error is already alerted in AuthContext's signUp.
      // setError(e.message || 'Registration failed. Please try again.');
      console.log("RegisterScreen: Registration attempt failed.");
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === "ios" ? "padding" : "height"}
      style={commonStyles.container}
    >
      <ScrollView contentContainerStyle={{ flexGrow: 1, justifyContent: 'center' }}>
        <View style={{alignItems: 'center', marginBottom: 30}}>
            <Ionicons name="person-add-outline" size={64} color={commonStyles.button.backgroundColor} />
        </View>
        <Text style={commonStyles.title}>Create Account</Text>

        {error ? <Text style={commonStyles.errorText}>{error}</Text> : null}

        <TextInput 
            style={commonStyles.input} 
            placeholder="Full Name" 
            value={name} 
            onChangeText={setName} 
            autoCapitalize="words"
            textContentType="name"
        />
        <TextInput 
            style={commonStyles.input} 
            placeholder="Email Address" 
            value={email} 
            onChangeText={setEmail} 
            keyboardType="email-address" 
            autoCapitalize="none" 
            autoComplete="email"
            textContentType="emailAddress"
        />
        <TextInput 
            style={commonStyles.input} 
            placeholder="Password (min. 6 characters)" 
            value={password} 
            onChangeText={setPassword} 
            secureTextEntry 
            textContentType="newPassword" // Helps password managers
        />
        <TextInput 
            style={commonStyles.input} 
            placeholder="Confirm Password" 
            value={passwordConfirmation} 
            onChangeText={setPasswordConfirmation} 
            secureTextEntry 
            textContentType="newPassword"
        />
        <TouchableOpacity 
            style={[commonStyles.button, isLoading && commonStyles.buttonDisabled]} 
            onPress={handleRegister} 
            disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator color={commonStyles.buttonText.color} />
          ) : (
            <Text style={commonStyles.buttonText}>Register</Text>
          )}
        </TouchableOpacity>
        <TouchableOpacity onPress={() => navigation.navigate('Login')}>
          <Text style={commonStyles.linkText}>Already have an account? Login</Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}