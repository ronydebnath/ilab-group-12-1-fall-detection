// import React, { createContext, useState, useEffect, useContext, useCallback, useMemo } from 'react';
// import { Alert, Text, View } from 'react-native'; 
// import apiService from '../api/apiService';
// import { playAlertSound, stopAlertSound, loadAlertSound, configureAudioMode } from '../services/soundService';
// import { requestSensorPermissions as requestSensors } from '../services/permissionService';
// import { fetchWeatherNSW as fetchWeather } from '../services/weatherService';
// import { useAuth } from './AuthContext';
// import { ALERT_ESCALATION_TIMEOUT_MS } from '../constants/appConstants';

// const AppContext = createContext(null);

// export const AppProvider = ({ children }) => {
//   const { userToken, userData } = useAuth();

//   // State
//   const [isFallAlertActive, setIsFallAlertActive] = useState(false);
//   const [currentFallEventId, setCurrentFallEventId] = useState(null); // Stores ID of the event created by createFallEvent
//   const [sensorPermissions, setSensorPermissions] = useState({ accelerometer: false, gyroscope: false });
//   const [systemStatus, setSystemStatus] = useState({ message: "Initializing system...", color: "orange" });
//   const [weatherData, setWeatherData] = useState(null);

//   // State for Escalation
//   const [escalationTimerId, setEscalationTimerId] = useState(null);
//   const [isEscalated, setIsEscalated] = useState(false);
//   const [escalationMessage, setEscalationMessage] = useState('');
//   const [fallDetectionTime, setFallDetectionTime] = useState(null); // Time of initial local alert trigger

//   const initializeAppSettings = useCallback(async () => {
//     console.log("AppContext: Initializing app settings...");
//     try {
//       const permissions = await requestSensors();
//       setSensorPermissions(permissions);
//       if (!permissions.accelerometer || !permissions.gyroscope) {
//         setSystemStatus({ message: "Sensor Permissions Denied. Core features may be limited.", color: "red" });
//       } else {
//         setSystemStatus({ message: "System Ready. Sensors Active.", color: "green" });
//       }
//       const weather = await fetchWeather();
//       setWeatherData(weather);
//     } catch (error) {
//       console.error("AppContext: Error initializing app settings", error);
//       setSystemStatus({ message: "Failed to initialize system settings.", color: "red" });
//     }
//   }, []);

//   useEffect(() => {
//     configureAudioMode();
//     loadAlertSound();
//     initializeAppSettings();
//   }, [initializeAppSettings]);

//   // Effect to handle the escalation timer
//   useEffect(() => {
//     let timerId = null;
//     console.log(`AppContext Timer Effect: isFallAlertActive=${isFallAlertActive}, isEscalated=${isEscalated}, currentFallEventId=${currentFallEventId}`);

//     if (isFallAlertActive && !isEscalated && currentFallEventId) { // Timer starts if alert is active and we have an event ID
//       console.log(`AppContext Timer Effect: Starting ${ALERT_ESCALATION_TIMEOUT_MS / 1000}s escalation timer for event ID: ${currentFallEventId}.`);
//       timerId = setTimeout(async () => {
//         console.log("AppContext Timer Effect: ==> Timeout EXPIRED. Escalating alert. <==");
//         // NO API call here to change status. The event is already logged.
//         // Backend logic will determine that an un-resolved event is a confirmed fall.

//         const detectedTimeFormatted = fallDetectionTime
//           ? fallDetectionTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
//           : 'an unknown time';
//         // The event ID here is the one from the initial createFallEvent call
//         const message = `A fall was detected at ${detectedTimeFormatted}. Your carer has been informed and help is on the way. (Event ID: ${currentFallEventId || 'N/A'})`;

//         setEscalationMessage(message);
//         setIsEscalated(true);
//         stopAlertSound(); 
//         console.log("AppContext Timer Effect: Carer has been informed (implicitly, as event was not marked false).");

//       }, ALERT_ESCALATION_TIMEOUT_MS);
//       setEscalationTimerId(timerId);
//       console.log("AppContext Timer Effect: Timer ID set:", timerId);
//     } else if (isFallAlertActive && !isEscalated && !currentFallEventId) {
//         console.warn("AppContext Timer Effect: Fall alert active, but cannot start escalation timer due to missing currentFallEventId.");
//     }

//     return () => {
//       if (timerId) {
//         console.log("AppContext Timer Effect: Cleanup clearing timer ID:", timerId);
//         clearTimeout(timerId);
//         setEscalationTimerId(null);
//       } else {
//         console.log("AppContext Timer Effect: Cleanup called, no active timerId to clear.");
//       }
//     };
//   }, [isFallAlertActive, isEscalated, fallDetectionTime, currentFallEventId]); // Removed userToken as it's not directly used for API call here

//   // Called by simulateFallApiCall AFTER the initial event is created on the backend
//   const triggerFallAlert = useCallback((eventIdFromApi) => {
//     console.log(`AppContext: Triggering local fall alert for event ID received from API: ${eventIdFromApi}`);
//     const now = new Date();
//     setFallDetectionTime(now);
//     setCurrentFallEventId(eventIdFromApi); // Store the ID of the event created on the backend
//     setIsEscalated(false); 
//     setEscalationMessage('');
//     setIsFallAlertActive(true); // This starts the UI alert and the escalation timer
//     playAlertSound();
//   }, []);

//   // This is for "I'm Safe" pressed BEFORE escalation
//   const resolveFallAlert = useCallback(async () => { 
//     console.log("AppContext: resolveFallAlert called (I'm Safe - pre-escalation). currentFallEventId:", currentFallEventId);
//     if (escalationTimerId) {
//       console.log("AppContext: Clearing escalation timer due to 'I'm Safe'. ID:", escalationTimerId);
//       clearTimeout(escalationTimerId);
//       setEscalationTimerId(null);
//     }

//     // Local state updates
//     setIsFallAlertActive(false);
//     setIsEscalated(false); 
//     setEscalationMessage('');
//     stopAlertSound();

//     if (!userToken) {
//       Alert.alert("Authentication Error", "You must be logged in to resolve an alert.");
//       setCurrentFallEventId(null); // Clear event ID
//       return;
//     }
//     if (!currentFallEventId) {
//       console.warn("AppContext: ResolveFallAlert called without a currentFallEventId to mark as false alarm on server.");
//       return; 
//     }

//     console.log(`AppContext: Marking fall event ID ${currentFallEventId} as FALSE_ALARM on the server.`);
//     try {
//       // API call to mark the event as a false alarm.
//       // This function should update the status of the *existing* event.
//       // The payload for markFalseAlarm might just be the event ID, or it might accept notes/status.
//       // Adjust apiService.markFalseAlarm as needed.
//       await apiService.markFalseAlarm(userToken, currentFallEventId, { 
//           notes: "User pressed 'I'm Safe' within the timeout." 
//           // Your backend might automatically set the status to 'false_alarm' upon calling this endpoint
//           // or you might need to send { status: 'FALSE_ALARM' } in the payload.
//       });
//       Alert.alert("Alert Resolved", `The event (ID: ${currentFallEventId}) has been successfully marked as a false alarm.`);
//     } catch (error) {
//       console.error("AppContext: Failed to mark event as false alarm on the server:", error.message, error.response?.data);
//       Alert.alert("Resolution Error", `Failed to mark alert as false alarm on the server: ${error.message}`);
//     } finally {
//       setCurrentFallEventId(null); // Clear event ID after attempting to resolve
//     }
//   }, [userToken, currentFallEventId, escalationTimerId]);

//   // For "Go to Home" button after escalation
//   const dismissEscalatedAlert = useCallback(() => {
//     console.log("AppContext: dismissEscalatedAlert called.");
//     setIsFallAlertActive(false); 
//     setIsEscalated(false);       
//     setEscalationMessage('');
//     setCurrentFallEventId(null); // Clear the event ID
//     // Sound should have already been stopped by the escalation timer.
//   }, []);

//   const simulateFallApiCall = useCallback(async () => {
//     console.log("AppContext: ========== simulateFallApiCall: TRIGGERED ==========");
//     if (!userToken) {
//       Alert.alert("Authentication Error", "You must be logged in to simulate a fall event.");
//       return;
//     }
//     const profileId = userData?.id || userData?.profile_id;
//     if (!profileId) {
//       Alert.alert("User Profile Error", "User profile ID not found. Cannot simulate fall.");
//       return;
//     }

//     Alert.alert(
//       "Simulate Fall Event?",
//       "This will log a new fall event on the server, then start a 30-second local alert. If not dismissed, carers will be notified (by backend logic). If dismissed, the event will be marked as a false alarm. Proceed?",
//       [
//         { text: "Cancel", style: "cancel" },
//         {
//           text: "Yes, Simulate",
//           style: "destructive",
//           onPress: async () => {
//             try {
//               // Step 1: Create the initial fall event log on the server.
//               // This payload should NOT contain a status that implies "false alarm" or "confirmed".
//               // The backend should assign a default initial status (e.g., "pending", "active_alert").
//               const initialFallDataPayload = {
//                 elderly_id: profileId,
//                 detected_at: new Date().toISOString(),
//                 sensor_data: { acc_x: 0.2, acc_y: 0.1, acc_z: 9.7 }, // Mock sensor data
//                 notes: "Simulated fall event initiated via app.",
//                 // DO NOT send a 'status' here, or send an initial one like 'PENDING_CONFIRMATION'
//                 // if your backend requires it for creation.
//               };
//               console.log("AppContext: Creating initial fall event log with payload:", JSON.stringify(initialFallDataPayload, null, 2));
//               const response = await apiService.createFallEvent(userToken, initialFallDataPayload);
//               const createdEventId = response?.data?.id || response?.id;

//               if (createdEventId) {
//                 console.log("AppContext: Initial fall event logged on server. Event ID:", createdEventId);
//                 // Step 2: Trigger the local alert UI and 30s timer, passing the new event ID
//                 triggerFallAlert(createdEventId); 
//                 Alert.alert("Fall Event Logged & Local Alert Triggered", `An event (ID: ${createdEventId}) has been logged. The 30-second countdown has started.`);
//               } else {
//                 console.error("AppContext: Simulation Error - Fall event logged on server, but no event ID returned in response.", response);
//                 Alert.alert("Simulation Error", "Fall event may have been logged on server, but no event ID was returned. Cannot proceed with local alert correctly.");
//               }
//             } catch (error) {
//               console.error("AppContext: Simulation API Failed (createFallEvent):", error.message, error.response?.data);
//               Alert.alert("Simulation API Failed", `Could not log initial fall event via API: ${error.message}`);
//             }
//           },
//         },
//       ]
//     );
//   }, [userToken, userData, triggerFallAlert]);

//   const fetchWeatherNSW = useCallback(async () => {
//     console.log("AppContext: Manually refreshing weather data...");
//     try {
//       const weather = await fetchWeather();
//       setWeatherData(weather);
//     } catch (error) {
//       console.error("AppContext: Failed to fetch weather", error);
//       setWeatherData(null);
//     }
//   }, []);

//   const appContextValue = useMemo(() => ({
//     isFallAlertActive,
//     currentFallEventId,
//     sensorPermissions,
//     systemStatus,
//     weatherData,
//     isEscalated,
//     escalationMessage,
//     requestSensorPermissions: initializeAppSettings,
//     fetchWeatherNSW,
//     triggerFallAlert,
//     resolveFallAlert,
//     dismissEscalatedAlert,
//     simulateFallApiCall,
//   }), [
//     isFallAlertActive, currentFallEventId, sensorPermissions, systemStatus, weatherData,
//     isEscalated, escalationMessage,
//     initializeAppSettings, fetchWeatherNSW, triggerFallAlert, resolveFallAlert, dismissEscalatedAlert, simulateFallApiCall
//   ]);

//   // We need to ensure all children are wrapped correctly - this is the fix for the error
//   return (
//     <AppContext.Provider value={appContextValue}>
//       {typeof children === 'string' ? <Text>{children}</Text> : children}
//     </AppContext.Provider>
//   );
// };

// export const useAppContext = () => {
//   const context = useContext(AppContext);
//   if (context === undefined || context === null) {
//     throw new Error('useAppContext must be used within an AppProvider');
//   }
//   return context;
// };

// import React, { createContext, useState, useEffect, useContext, useCallback, useMemo } from 'react';
// import { Alert } from 'react-native';
// import apiService from '../api/apiService'; // Your API service
// import { playAlertSound, stopAlertSound, loadAlertSound, configureAudioMode } from '../services/soundService';
// import { requestSensorPermissions as requestSensors } from '../services/permissionService';
// import { fetchWeatherNSW as fetchWeather } from '../services/weatherService';
// import { useAuth } from './AuthContext';

// const AppContext = createContext(null);

// const ALERT_ESCALATION_TIMEOUT_MS = 30000; // 30 seconds

// const STATUS_CONFIRMED_FALL = 'CONFIRMED_FALL'; // Or your backend's string
// const STATUS_FALSE_ALARM = 'FALSE_ALARM';       // Or your backend's string

// export const AppProvider = ({ children }) => {
//   const { userToken, userData } = useAuth(); 

//   console.log("AppContext: Provider rendering. userData from useAuth():", JSON.stringify(userData, null, 2));

//   const [isFallAlertActive, setIsFallAlertActive] = useState(false);
//   const [pendingFallData, setPendingFallData] = useState(null);
//   const [sensorPermissions, setSensorPermissions] = useState({ accelerometer: false, gyroscope: false });
//   const [systemStatus, setSystemStatus] = useState({ message: "Initializing system...", color: "orange" });
//   const [weatherData, setWeatherData] = useState(null);

//   const [escalationTimerId, setEscalationTimerId] = useState(null);
//   const [isEscalated, setIsEscalated] = useState(false);
//   const [escalationMessage, setEscalationMessage] = useState('');

//   const initializeAppSettings = useCallback(async () => {
//     console.log("AppContext: Initializing app settings...");
//     try {
//       const permissions = await requestSensors();
//       setSensorPermissions(permissions);
//       if (!permissions.accelerometer || !permissions.gyroscope) {
//         setSystemStatus({ message: "Sensor Permissions Denied. Core features may be limited.", color: "red" });
//       } else {
//         setSystemStatus({ message: "System Ready. Sensors Active.", color: "green" });
//       }
//       const weather = await fetchWeather();
//       setWeatherData(weather);
//     } catch (error) {
//       console.error("AppContext: Error initializing app settings", error);
//       setSystemStatus({ message: "Failed to initialize system settings.", color: "red" });
//     }
//   }, []);

//   useEffect(() => {
//     configureAudioMode();
//     loadAlertSound();
//     initializeAppSettings();
//   }, [initializeAppSettings]);

//   useEffect(() => {
//     let timerId = null;
//     console.log(`AppContext Timer Effect: isFallAlertActive=${isFallAlertActive}, isEscalated=${isEscalated}, pendingFallData exists=${!!pendingFallData}`);

//     if (isFallAlertActive && !isEscalated && userToken && pendingFallData) {
//       console.log(`AppContext Timer Effect: Starting ${ALERT_ESCALATION_TIMEOUT_MS / 1000}s escalation timer.`);
//       timerId = setTimeout(async () => {
//         console.log("AppContext Timer Effect: ==> Timeout EXPIRED. Escalating alert. <==");
        
//         const fallDataToSubmit = {
//           ...pendingFallData, 
//           status: STATUS_CONFIRMED_FALL,
//           notes: `${pendingFallData.notes || ''} User did not respond to alert within ${ALERT_ESCALATION_TIMEOUT_MS / 1000} seconds.`.trim()
//         };

//         try {
//           console.log(`AppContext: Creating fall event with status '${STATUS_CONFIRMED_FALL}'. Payload:`, JSON.stringify(fallDataToSubmit, null, 2));
//           const response = await apiService.createFallEvent(userToken, fallDataToSubmit);
//           const createdEventId = response?.data?.id || response?.id;
//           console.log(`AppContext: Successfully created event ${createdEventId} with status ${STATUS_CONFIRMED_FALL}.`);

//           const detectedTimeFormatted = pendingFallData.detected_at
//             ? new Date(pendingFallData.detected_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
//             : 'an unknown time';
//           const message = `A fall was detected at ${detectedTimeFormatted}. Your carer has been informed. (Event ID: ${createdEventId || 'N/A'})`;

//           setEscalationMessage(message);
//           setIsEscalated(true);
//           stopAlertSound(); 
//           console.log("AppContext Timer Effect: Carer has been informed (via backend creation of event).");

//         } catch (apiError) {
//           console.error("AppContext Timer Effect: Failed to create fall event on server for escalation:", apiError.message, apiError.response?.data);
//           const detectedTimeFormatted = pendingFallData.detected_at
//             ? new Date(pendingFallData.detected_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
//             : 'an unknown time';
//           const message = `A fall was detected at ${detectedTimeFormatted}. Could not confirm with server, but local alert escalated.`;
//           setEscalationMessage(message);
//           setIsEscalated(true);
//           stopAlertSound();
//           Alert.alert("Escalation Error", "Could not create the fall event on the server. Please check your connection.");
//         } finally {
//           setPendingFallData(null); 
//         }
//       }, ALERT_ESCALATION_TIMEOUT_MS);
//       setEscalationTimerId(timerId);
//       console.log("AppContext Timer Effect: Timer ID set:", timerId);
//     } else if (isFallAlertActive && !isEscalated && (!userToken || !pendingFallData)) {
//         console.warn("AppContext Timer Effect: Fall alert active, but cannot start escalation timer due to missing userToken or pendingFallData.");
//     }

//     return () => {
//       if (timerId) {
//         console.log("AppContext Timer Effect: Cleanup clearing timer ID:", timerId);
//         clearTimeout(timerId);
//         setEscalationTimerId(null);
//       } else {
//         console.log("AppContext Timer Effect: Cleanup called, no active timerId to clear.");
//       }
//     };
//   }, [isFallAlertActive, isEscalated, pendingFallData, userToken]);

//   const triggerFallAlert = useCallback((initialFallData) => {
//     console.log(`AppContext: Triggering local fall alert with initial data (should include correct elderly_id):`, JSON.stringify(initialFallData, null, 2));
//     setPendingFallData(initialFallData);
//     setIsEscalated(false); 
//     setEscalationMessage('');
//     setIsFallAlertActive(true);
//     playAlertSound();
//   }, []);

//   const resolveFallAlert = useCallback(async () => { 
//     console.log("AppContext: resolveFallAlert called (I'm Safe - pre-escalation).");
//     if (escalationTimerId) {
//       console.log("AppContext: Clearing escalation timer due to 'I'm Safe'. ID:", escalationTimerId);
//       clearTimeout(escalationTimerId);
//       setEscalationTimerId(null);
//     }

//     setIsFallAlertActive(false);
//     setIsEscalated(false); 
//     setEscalationMessage('');
//     stopAlertSound();

//     if (!userToken) {
//       Alert.alert("Authentication Error", "You must be logged in to resolve an alert.");
//       setPendingFallData(null);
//       return;
//     }
//     if (!pendingFallData) {
//       console.warn("AppContext: ResolveFallAlert called without pendingFallData to create a 'false alarm' event.");
//       return; 
//     }

//     const falseAlarmData = {
//       ...pendingFallData, 
//       status: STATUS_FALSE_ALARM,
//       notes: `${pendingFallData.notes || ''} User pressed 'I'm Safe' within the timeout.`.trim(),
//     };

//     console.log(`AppContext: Preparing to create fall event with status '${STATUS_FALSE_ALARM}'. Payload:`, JSON.stringify(falseAlarmData, null, 2));
    
//     try {
//       const response = await apiService.createFallEvent(userToken, falseAlarmData);
//       const createdEventId = response?.data?.id || response?.id;
//       console.log(`AppContext: API call for '${STATUS_FALSE_ALARM}' successful. Event ID: ${createdEventId || 'N/A'}`);
//       Alert.alert("Alert Resolved", `The event (ID: ${createdEventId || 'N/A'}) has been successfully marked as a false alarm.`);
//     } catch (error) {
//       console.error("AppContext: Failed to create 'false alarm' event on the server:", error.message, error.response?.data);
//       Alert.alert("Resolution Error", `Failed to record 'false alarm' on the server: ${error.message}`);
//     } finally {
//       setPendingFallData(null); 
//     }
//   }, [userToken, pendingFallData, escalationTimerId]);

//   const dismissEscalatedAlert = useCallback(() => {
//     console.log("AppContext: dismissEscalatedAlert called.");
//     setIsFallAlertActive(false); 
//     setIsEscalated(false);       
//     setEscalationMessage('');
//   }, []);

//   const simulateFallApiCall = useCallback(async () => {
//     console.log("AppContext: ========== simulateFallApiCall: TRIGGERED (Local Only First) ==========");
//     console.log("AppContext: simulateFallApiCall - userData from useAuth():", JSON.stringify(userData, null, 2));

//     if (!userToken) {
//       Alert.alert("Authentication Error", "You must be logged in to simulate a fall event.");
//       return;
//     }
    
//     // =======================================================================
//     // === CRITICAL CHANGE: Extract elderly_id from userData.elderly_profile.id ===
//     // =======================================================================
//     const elderlyProfileId = userData?.elderly_profile?.id; 
//     console.log("AppContext: simulateFallApiCall - Extracted elderlyProfileId for elderly_id:", elderlyProfileId);

//     if (!elderlyProfileId) {
//       Alert.alert("User Profile Error", "Elderly profile ID not found in current user data. Cannot simulate fall.");
//       console.error("AppContext: simulateFallApiCall - elderlyProfileId is missing. userData was:", JSON.stringify(userData, null, 2));
//       return;
//     }
//     // =======================================================================

//     Alert.alert(
//       "Simulate Local Fall Alert?",
//       `This will start a 30-second local alert for elderly profile ID ${elderlyProfileId}. If not dismissed, a '${STATUS_CONFIRMED_FALL}' event will be created. If dismissed, a '${STATUS_FALSE_ALARM}' event will be created. Proceed?`,
//       [
//         { text: "Cancel", style: "cancel" },
//         {
//           text: "Yes, Simulate Local Alert",
//           style: "destructive",
//           onPress: () => {
//             console.log("AppContext: User confirmed local fall simulation for elderly_id:", elderlyProfileId);
//             const initialFallData = {
//               elderly_id: elderlyProfileId, // Use the ID from the elderly_profile
//               detected_at: new Date().toISOString(),
//               sensor_data: { acc_x: 0.2, acc_y: 0.1, acc_z: 9.7 },
//               notes: "Simulated fall event initiated locally.",
//             };
//             triggerFallAlert(initialFallData);
//             Alert.alert("Local Alert Triggered", "The 30-second countdown has started. Respond via the alert screen.");
//           },
//         },
//       ]
//     );
//   }, [userToken, userData, triggerFallAlert]); // userData is now a dependency

//   const fetchWeatherNSW = useCallback(async () => {
//     console.log("AppContext: Manually refreshing weather data...");
//     try {
//       const weather = await fetchWeather();
//       setWeatherData(weather);
//     } catch (error) {
//       console.error("AppContext: Failed to fetch weather", error);
//       setWeatherData(null);
//     }
//   }, []);

//   const appContextValue = useMemo(() => ({
//     isFallAlertActive,
//     pendingFallData,
//     sensorPermissions,
//     systemStatus,
//     weatherData,
//     isEscalated,
//     escalationMessage,
//     requestSensorPermissions: initializeAppSettings,
//     fetchWeatherNSW,
//     triggerFallAlert,
//     resolveFallAlert,
//     dismissEscalatedAlert,
//     simulateFallApiCall,
//   }), [
//     isFallAlertActive, pendingFallData, sensorPermissions, systemStatus, weatherData,
//     isEscalated, escalationMessage,
//     initializeAppSettings, fetchWeatherNSW, triggerFallAlert, resolveFallAlert, dismissEscalatedAlert, simulateFallApiCall
//   ]);

//   return <AppContext.Provider value={appContextValue}>{children}</AppContext.Provider>;
// };

// export const useAppContext = () => {
//   const context = useContext(AppContext);
//   if (context === undefined || context === null) {
//     throw new Error('useAppContext must be used within an AppProvider');
//   }
//   return context;
// };

import React, { createContext, useState, useEffect, useContext, useCallback, useMemo } from 'react';
import { Alert } from 'react-native';
import apiService from '../api/apiService'; // Your API service
import { playAlertSound, stopAlertSound, loadAlertSound, configureAudioMode } from '../services/soundService';
import { requestSensorPermissions as requestSensors } from '../services/permissionService';
import { fetchWeatherNSW as fetchWeather } from '../services/weatherService';
import { useAuth } from './AuthContext';

const AppContext = createContext(null);

const ALERT_ESCALATION_TIMEOUT_MS = 30000; // 30 seconds

// Note: Status strings are now primarily managed by the backend based on which API is called
// or by backend logic (e.g., if an event isn't marked false, it's considered confirmed).
// The frontend mainly needs to know which API endpoint to call.

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
        
        // NO API call here to change status. The event is already logged.
        // Backend logic will determine that an un-resolved event is a confirmed fall.

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
          // If your backend's markFalseAlarm endpoint itself sets the status to "false alarm",
          // you don't need to send 'status' in this payload.
          // If it requires a status, add it: status: 'FALSE_ALARM' (using your backend's expected string)
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
      `This will log a new fall event on the server for elderly profile ID ${elderlyProfileId}, then start a 30-second local alert. If not dismissed, carers will be notified (by backend logic based on the logged event). If dismissed by 'I'm Safe', the logged event will be updated to a false alarm. Proceed?`,
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
                Alert.alert("Fall Event Logged & Local Alert Triggered", `An event (ID: ${createdEventId}) has been logged. The 30-second countdown has started.`);
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