import React, { createContext, useState, useEffect, useContext, useCallback, useMemo } from 'react';
import { Alert } from 'react-native';
import apiService from '../api/apiService'; // Your API service
import { playAlertSound, stopAlertSound, loadAlertSound, configureAudioMode } from '../services/soundService';
import { requestSensorPermissions as requestSensors } from '../services/permissionService';
import { fetchWeatherNSW as fetchWeather } from '../services/weatherService';
import { useAuth } from './AuthContext';

const AppContext = createContext(null);

const ALERT_ESCALATION_TIMEOUT_MS = 10000; // 30 seconds

export const AppProvider = ({ children }) => {
  const { userToken, userData } = useAuth(); 

  console.log("AppContext: Provider rendering. userData from useAuth():", JSON.stringify(userData, null, 2));

  const [isFallAlertActive, setIsFallAlertActive] = useState(false);
  const [currentFallEventId, setCurrentFallEventId] = useState(null); // Stores ID of the event created by createFallEvent
  const [sensorPermissions, setSensorPermissions] = useState({ accelerometer: false, gyroscope: false });
  const [systemStatus, setSystemStatus] = useState({ message: "Initializing system...", color: "orange" });
  const [weatherData, setWeatherData] = useState(null);

  const [escalationTimerId, setEscalationTimerId] = useState(null);
  const [isEscalated, setIsEscalated] = useState(false);
  const [escalationMessage, setEscalationMessage] = useState('');
  const [fallDetectionTime, setFallDetectionTime] = useState(null); // Time of initial local alert trigger

  const initializeAppSettings = useCallback(async () => {
    console.log("AppContext: Initializing app settings...");
    try {
      const permissions = await requestSensors();
      setSensorPermissions(permissions);
      if (!permissions.accelerometer || !permissions.gyroscope) {
        setSystemStatus({ message: "Sensor Permissions Denied. Core features may be limited.", color: "red" });
      } else {
        setSystemStatus({ message: "System Ready. Sensors Active.", color: "green" });
      }
      const weather = await fetchWeather();
      setWeatherData(weather);
    } catch (error) {
      console.error("AppContext: Error initializing app settings", error);
      setSystemStatus({ message: "Failed to initialize system settings.", color: "red" });
    }
  }, []);

  useEffect(() => {
    configureAudioMode();
    loadAlertSound();
    initializeAppSettings();
  }, [initializeAppSettings]);

  // Effect to handle the escalation timer
  useEffect(() => {
    let timerId = null;
    console.log(`AppContext Timer Effect: isFallAlertActive=${isFallAlertActive}, isEscalated=${isEscalated}, currentFallEventId=${currentFallEventId}`);

    if (isFallAlertActive && !isEscalated && currentFallEventId) { 
      console.log(`AppContext Timer Effect: Starting ${ALERT_ESCALATION_TIMEOUT_MS / 1000}s escalation timer for event ID: ${currentFallEventId}.`);
      timerId = setTimeout(async () => { // Keep async if any cleanup needs it, though API call removed
        console.log("AppContext Timer Effect: ==> Timeout EXPIRED. Escalating alert locally. <==");
        
        const detectedTimeFormatted = fallDetectionTime
          ? fallDetectionTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
          : 'an unknown time';
        // The event ID here is the one from the initial createFallEvent call
        const message = `A fall was detected at ${detectedTimeFormatted}. Your carer has been informed. (Event ID: ${currentFallEventId || 'N/A'})`;

        setEscalationMessage(message);
        setIsEscalated(true);
        stopAlertSound(); 
        console.log("AppContext Timer Effect: Carer has been informed (implicitly, as event was not marked false by user).");

      }, ALERT_ESCALATION_TIMEOUT_MS);
      setEscalationTimerId(timerId);
      console.log("AppContext Timer Effect: Timer ID set:", timerId);
    } else if (isFallAlertActive && !isEscalated && !currentFallEventId) {
        console.warn("AppContext Timer Effect: Fall alert active, but cannot start escalation timer due to missing currentFallEventId.");
    }

    return () => {
      if (timerId) {
        console.log("AppContext Timer Effect: Cleanup clearing timer ID:", timerId);
        clearTimeout(timerId);
        setEscalationTimerId(null);
      } else {
        console.log("AppContext Timer Effect: Cleanup called, no active timerId to clear.");
      }
    };
  }, [isFallAlertActive, isEscalated, fallDetectionTime, currentFallEventId]);

  // Called by simulateFallApiCall AFTER the initial event is created on the backend
  const triggerFallAlert = useCallback((eventIdFromApi) => {
    console.log(`AppContext: Triggering local fall alert for event ID received from API: ${eventIdFromApi}`);
    const now = new Date();
    setFallDetectionTime(now);
    setCurrentFallEventId(eventIdFromApi); // Store the ID of the event created on the backend
    setIsEscalated(false); 
    setEscalationMessage('');
    setIsFallAlertActive(true); // This starts the UI alert and the escalation timer
    playAlertSound();
  }, []);

  // This is for "I'm Safe" pressed BEFORE escalation
  const resolveFallAlert = useCallback(async () => { 
    console.log("AppContext: resolveFallAlert called (I'm Safe - pre-escalation). currentFallEventId:", currentFallEventId);
    if (escalationTimerId) {
      console.log("AppContext: Clearing escalation timer due to 'I'm Safe'. ID:", escalationTimerId);
      clearTimeout(escalationTimerId);
      setEscalationTimerId(null);
    }

    // Local state updates
    setIsFallAlertActive(false);
    setIsEscalated(false); 
    setEscalationMessage('');
    stopAlertSound();

    if (!userToken) {
      Alert.alert("Authentication Error", "You must be logged in to resolve an alert.");
      setCurrentFallEventId(null); 
      return;
    }
    if (!currentFallEventId) {
      console.warn("AppContext: ResolveFallAlert called without a currentFallEventId to mark as false alarm on server.");
      return; 
    }

    console.log(`AppContext: Marking fall event ID ${currentFallEventId} as FALSE_ALARM on the server.`);
    try {
      // API call to mark the existing event as a false alarm.
      // Your apiService.markFalseAlarm should make a PUT/PATCH request to update the status.
      // The payload might just be notes, or it might also include { status: 'YOUR_FALSE_ALARM_STATUS_STRING' }
      // if your backend endpoint for marking false alarm expects a status field.
      // For this example, let's assume it takes a notes object.
      await apiService.markFalseAlarm(userToken, currentFallEventId, { 
          notes: "User pressed 'I'm Safe' within the timeout." 
      });
      Alert.alert("Alert Resolved", `The event (ID: ${currentFallEventId}) has been successfully marked as a false alarm.`);
    } catch (error) {
      console.error("AppContext: Failed to mark event as false alarm on the server:", error.message, error.response?.data);
      Alert.alert("Resolution Error", `Failed to mark alert as false alarm on the server: ${error.message}`);
    } finally {
      setCurrentFallEventId(null); // Clear event ID after attempting to resolve
    }
  }, [userToken, currentFallEventId, escalationTimerId]);

  // For "Go to Home Page" button after escalation
  const dismissEscalatedAlert = useCallback(() => {
    console.log("AppContext: dismissEscalatedAlert called.");
    setIsFallAlertActive(false); 
    setIsEscalated(false);       
    setEscalationMessage('');
    setCurrentFallEventId(null); 
  }, []);

  const simulateFallApiCall = useCallback(async () => {
    console.log("AppContext: ========== simulateFallApiCall: TRIGGERED ==========");
    console.log("AppContext: simulateFallApiCall - userData from useAuth():", JSON.stringify(userData, null, 2));

    if (!userToken) {
      Alert.alert("Authentication Error", "You must be logged in to simulate a fall event.");
      return;
    }
    
    const elderlyProfileId = userData?.elderly_profile?.id; 
    console.log("AppContext: simulateFallApiCall - Extracted elderlyProfileId for elderly_id:", elderlyProfileId);

    if (!elderlyProfileId) {
      Alert.alert("User Profile Error", "Elderly profile ID not found in current user data. Cannot simulate fall.");
      console.error("AppContext: simulateFallApiCall - elderlyProfileId is missing. userData was:", JSON.stringify(userData, null, 2));
      return;
    }

    Alert.alert(
      "Simulate Fall Event?",
      'This will log a new fall event on the database and trigger a local alert. If not dismissed, carers will be notified.',
      // `This will log a new fall event on the server for elderly profile ID ${elderlyProfileId}, then start a 30-second local alert. If not dismissed, carers will be notified (by backend logic based on the logged event). If dismissed by 'I'm Safe', the logged event will be updated to a false alarm. Proceed?`,
      [
        { text: "Cancel", style: "cancel" },
        {
          text: "Yes, Simulate",
          style: "destructive",
          onPress: async () => {
            try {
              // Step 1: Create the initial fall event log on the server.
              // This payload should NOT contain a status that implies "false alarm" or "confirmed".
              // The backend should assign a default initial status (e.g., "pending_confirmation", "active_alert").
              const initialFallDataPayload = {
                elderly_id: elderlyProfileId,
                detected_at: new Date().toISOString(),
                sensor_data: { acc_x: 0.2, acc_y: 0.1, acc_z: 9.7 },
                notes: "Simulated fall event initiated via app.",
                // DO NOT send a 'status' here, or send an initial one like 'PENDING_CONFIRMATION'
                // if your backend requires it for creation. The backend should set a default.
              };
              console.log("AppContext: Creating initial fall event log with payload:", JSON.stringify(initialFallDataPayload, null, 2));
              const response = await apiService.createFallEvent(userToken, initialFallDataPayload);
              const createdEventId = response?.data?.id || response?.id;

              if (createdEventId) {
                console.log("AppContext: Initial fall event logged on server. Event ID:", createdEventId);
                // Step 2: Trigger the local alert UI and 30s timer, passing the new event ID
                triggerFallAlert(createdEventId); 
                console.log("Fall Event Logged & Local Alert Triggered", `An event (ID: ${createdEventId}) has been logged. The 30-second countdown has started.`);
              } else {
                console.error("AppContext: Simulation Error - Fall event logged on server, but no event ID returned in response.", response);
                Alert.alert("Simulation Error", "Fall event may have been logged on server, but no event ID was returned. Cannot proceed with local alert correctly.");
              }
            } catch (error) {
              console.error("AppContext: Simulation API Failed (createFallEvent):", error.message, error.response?.data);
              Alert.alert("Simulation API Failed", `Could not log initial fall event via API: ${error.message}`);
            }
          },
        },
      ]
    );
  }, [userToken, userData, triggerFallAlert]);

  const fetchWeatherNSW = useCallback(async () => {
    console.log("AppContext: Manually refreshing weather data...");
    try {
      const weather = await fetchWeather();
      setWeatherData(weather);
    } catch (error) {
      console.error("AppContext: Failed to fetch weather", error);
      setWeatherData(null);
    }
  }, []);

  const appContextValue = useMemo(() => ({
    isFallAlertActive,
    currentFallEventId, 
    sensorPermissions,
    systemStatus,
    weatherData,
    isEscalated,
    escalationMessage,
    requestSensorPermissions: initializeAppSettings,
    fetchWeatherNSW,
    triggerFallAlert,
    resolveFallAlert,
    dismissEscalatedAlert,
    simulateFallApiCall,
  }), [
    isFallAlertActive, currentFallEventId, sensorPermissions, systemStatus, weatherData,
    isEscalated, escalationMessage,
    initializeAppSettings, fetchWeatherNSW, triggerFallAlert, resolveFallAlert, dismissEscalatedAlert, simulateFallApiCall
  ]);

  return <AppContext.Provider value={appContextValue}>{children}</AppContext.Provider>;
};

export const useAppContext = () => {
  const context = useContext(AppContext);
  if (context === undefined || context === null) {
    throw new Error('useAppContext must be used within an AppProvider');
  }
  return context;
};