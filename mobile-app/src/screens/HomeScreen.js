import React, { useState, useEffect, useCallback } from 'react';
import { ScrollView, View, Text, TouchableOpacity, ActivityIndicator, Button, RefreshControl, Platform, StyleSheet } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { useAuth } from '../contexts/AuthContext';
import { useAppContext } from '../contexts/AppContext';
// Import common styles - assuming commonStyles includes COLORS, typography, spacing, and card styles
import { commonStyles } from '../styles/commonStyles';

// Create local styles if needed
const localStyles = StyleSheet.create({
  // Style for the divider line within cards
  cardDivider: {
    height: 2, // Thin line
    backgroundColor: 'rgba(0, 0, 0, 0.2)',// Use border color from commonStyles
    marginVertical: commonStyles.spacing.small, // Add vertical space around the divider
    opacity: 10
  },
});


export default function HomeScreen({ navigation }) {
  console.log("--- HomeScreen rendering ---", new Date().toLocaleTimeString()); // Add a timestamp to see frequency
  // Get user data and authentication actions from AuthContext
  const { userData, fetchAndUpdateUser } = useAuth(); // Added fetchAndUpdateUser
  // Get app-specific state and actions from AppContext
  const {
    sensorPermissions,
    requestSensorPermissions, // This re-runs the permission check and updates AppContext
    systemStatus,
    weatherData,
    fetchWeatherNSW, // To allow manual refresh
    simulateFallApiCall,
  } = useAppContext();
  const [currentTime, setCurrentTime] = useState(new Date());
  const [refreshing, setRefreshing] = useState(false);

  // Effect to update current time every minute
  useEffect(() => {
    const timer = setInterval(() => setCurrentTime(new Date()), 1000 * 60); // Update every minute
    return () => clearInterval(timer); // Cleanup on unmount
  }, []);

  // Effect to fetch user data when the screen is focused (e.g., after login)
  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      console.log("HomeScreen focused, fetching data...");
      fetchAndUpdateUser(); // Fetch latest user data when screen comes into focus
      // Also refresh weather and permissions status if desired
      fetchWeatherNSW();
      requestSensorPermissions();
    });
    // Fetch data on initial mount as well
    fetchAndUpdateUser();
    fetchWeatherNSW();
    requestSensorPermissions();

    return unsubscribe; // Cleanup listener on unmount
  }, [navigation, fetchAndUpdateUser, fetchWeatherNSW, requestSensorPermissions]); // Dependencies for the effect


  const onRefresh = useCallback(async () => {
    setRefreshing(true); // Show the refreshing indicator
    try {
      // Fetch all necessary data in parallel
      await Promise.all([
        fetchAndUpdateUser(),
        fetchWeatherNSW(),
        requestSensorPermissions(), // This will also update systemStatus in AppContext
      ]);
      console.log("HomeScreen refreshed successfully.");
    } catch (error) {
      console.error("HomeScreen: Error during refresh:", error);
      // Optionally show an error message to the user
      Alert.alert("Refresh Failed", "Could not update data.");
    } finally {
      setRefreshing(false);
    }
  }, [fetchAndUpdateUser, fetchWeatherNSW, requestSensorPermissions]); // Dependencies for the callback

  const userName = userData?.name || userData?.email || 'User'; // Display name or email

  // Determine the color for the system status indicator based on the status message
  const getStatusColor = (statusMessage) => {
    switch (statusMessage) {
      case 'Ready':
        return commonStyles.COLORS.success; // Green for Ready
      case 'Initializing...':
        return commonStyles.COLORS.warning; // Orange for Initializing
      case 'Permissions Denied':
      case 'Error':
        return commonStyles.COLORS.danger; // Red for Denied or Error
      default:
        return commonStyles.COLORS.textSecondary; // Default to secondary text color
    }
  };

  // Determine the icon name for the system status based on the status color
  const getStatusIcon = (statusColor) => {
      switch (statusColor) {
          case commonStyles.COLORS.success:
              return "shield-checkmark-outline"; // Green -> Shield checkmark
          case commonStyles.COLORS.danger:
              return "alert-circle-outline"; // Red -> Alert circle
          case commonStyles.COLORS.warning:
              return "information-circle-outline"; // Orange -> Info circle
          default:
              return "information-circle-outline"; // Default -> Info circle
      }
  };

  return (
    // ScrollView to allow content to be scrollable if it exceeds screen height
    <ScrollView
      // Apply common styles for the scroll view container and content padding
      style={commonStyles.scrollViewContainer}
      contentContainerStyle={commonStyles.contentContainer}
      // Configure the pull-to-refresh functionality
      refreshControl={
        <RefreshControl
          refreshing={refreshing}
          onRefresh={onRefresh}
          // Use colors from common styles for the refresh indicator
          colors={[commonStyles.COLORS.primary]} // Android refresh indicator color
          tintColor={commonStyles.COLORS.primary} // iOS refresh indicator color
        />
      }
    >
      <Text style={commonStyles.greetingText}>Hello, {userName}!</Text>
      <View style={commonStyles.homeUserProfileCard}>
        <View style={commonStyles.rowSpaceBetween}>
            <Text style={commonStyles.homeUserProfileTitle}>User Profile</Text>
            <Ionicons name="person-circle-outline" size={28} color={commonStyles.iconStyle.color} />
        </View>
        <View style={localStyles.cardDivider} />
        <Text style={commonStyles.homeUserProfileText}>Name: {userData?.name || 'N/A'}</Text>
        <Text style={commonStyles.homeUserProfileText}>Email: {userData?.email || 'N/A'}</Text>
      </View>
      <View style={[commonStyles.homeInfoCard, { backgroundColor: commonStyles.COLORS.cardBlue }]}>
        <View style={commonStyles.rowSpaceBetween}>
            <Text style={commonStyles.cardTitle}>Current Time</Text>
            <Ionicons name="time-outline" size={24} color={commonStyles.iconStyle.color} />
        </View>
        <View style={localStyles.cardDivider} />
        <Text style={commonStyles.cardText}>{currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</Text>
        <Text style={commonStyles.cardText}>Date: {currentTime.toLocaleDateString()}</Text>
      </View>
      <View style={[commonStyles.homeInfoCard, { backgroundColor: commonStyles.COLORS.cardGreen }]}>
        <View style={commonStyles.rowSpaceBetween}>
          <Text style={commonStyles.cardTitle}>Weather in {weatherData?.city || 'NSW'}</Text>
          <TouchableOpacity onPress={fetchWeatherNSW} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
            <Ionicons name="refresh-outline" size={24} color={commonStyles.COLORS.primary} />
          </TouchableOpacity>
        </View>
        <View style={localStyles.cardDivider} />
        {weatherData ? (
          <>
            <Text style={commonStyles.cardText}>Temperature: {weatherData.temp}</Text>
            <Text style={commonStyles.cardText}>Condition: {weatherData.description}</Text>
          </>
        ) : (
          // Show activity indicator while fetching weather data
          <ActivityIndicator style={commonStyles.activityIndicator} color={commonStyles.COLORS.primary} />
        )}
      </View>
      <View style={[commonStyles.homeInfoCard, { backgroundColor: commonStyles.COLORS.cardYellow }]}>
        <View style={commonStyles.rowSpaceBetween}>
            <Text style={commonStyles.cardTitle}>System Status</Text>
            <Ionicons
                name={getStatusIcon(getStatusColor(systemStatus.message))} // Get icon based on color
                size={24}
                color={getStatusColor(systemStatus.message)} // Get color based on status message
            />
        </View>
        <View style={localStyles.cardDivider} />
        <Text style={[commonStyles.cardText, { color: getStatusColor(systemStatus.message), fontWeight: commonStyles.typography.fontWeightMedium }]}>{systemStatus.message}</Text>
      </View>
      <View style={[commonStyles.homeInfoCard, { backgroundColor: commonStyles.COLORS.cardRed }]}>
        <View style={commonStyles.rowSpaceBetween}>
            <Text style={commonStyles.cardTitle}>Sensor Permissions</Text>
            <Ionicons name="body-outline" size={24} color={commonStyles.iconStyle.color} />
        </View>
        <View style={localStyles.cardDivider} />
        <Text style={commonStyles.cardText}>Accelerometer: {sensorPermissions.accelerometer ? 'Granted' : 'Denied'}</Text>
        <Text style={commonStyles.cardText}>Gyroscope: {sensorPermissions.gyroscope ? 'Granted' : 'Denied'}</Text>
        {(!sensorPermissions.accelerometer || !sensorPermissions.gyroscope) && (
          // Using TouchableOpacity for consistent button styling
          <TouchableOpacity
            style={[commonStyles.button, { marginTop: commonStyles.spacing.medium }]} // Apply base button style and spacing
            onPress={requestSensorPermissions}
          >
            <Text style={commonStyles.buttonText}>Re-check Permissions</Text>
          </TouchableOpacity>
        )}
      </View>
      <View style={commonStyles.homeActionsContainer}>
        <TouchableOpacity
          style={[commonStyles.button, { backgroundColor: commonStyles.COLORS.warning, marginTop: 0 }]} // Removed top margin here, added to container
          onPress={simulateFallApiCall}
        >
          <View style={{flexDirection: 'row', alignItems: 'center'}}>
              <Text style={commonStyles.buttonText}>Simulate Fall Event (Test)</Text>
          </View>
        </TouchableOpacity>
        <TouchableOpacity
          // Combine base button style with a secondary color and spacing
          style={[commonStyles.button, { backgroundColor: commonStyles.COLORS.secondary, marginTop: commonStyles.spacing.medium }]} // Added top margin using spacing
          onPress={() => navigation.navigate('Settings')}
        >
          <View style={{flexDirection: 'row', alignItems: 'center'}}>
              <Ionicons name="settings-outline" size={20} color={commonStyles.buttonText.color} style={{marginRight: commonStyles.spacing.small}}/>
              <Text style={commonStyles.buttonText}>Go to Settings</Text>
          </View>
        </TouchableOpacity>
      </View>

    </ScrollView>
  );
}
