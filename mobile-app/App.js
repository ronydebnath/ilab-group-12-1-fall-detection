import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { AuthProvider } from './src/contexts/AuthContext';
import { AppProvider } from './src/contexts/AppContext';
import AppNavigator from './src/navigation/AppNavigator';

export default function App() {
  console.log("App.js rendering - AppProvider + AppNavigator test");
  return (
    <AuthProvider>
    <AppProvider> 
      <NavigationContainer>
        <AppNavigator />
      </NavigationContainer>
    </AppProvider>
    </AuthProvider>
  );
}