import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Alert,
  Linking,
  Platform,
  ActivityIndicator,
  Dimensions,
  StyleSheet // Keep StyleSheet for local styles if needed
} from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { useAppContext } from '../contexts/AppContext';
// Import the common styles, ensuring COLORS, typography, and spacing are included in the export
import { commonStyles } from '../styles/commonStyles'; // Ensure this path is correct
import { useNavigation } from '@react-navigation/native'; // Import useNavigation

// Create a local stylesheet for any AlertScreen-specific styles or overrides
const localStyles = StyleSheet.create({
  // Add any AlertScreen-specific styles here if needed
  // Example: If you wanted a specific margin for the initial warning icon
  warningIcon: {
    marginBottom: commonStyles.spacing?.large || 24, // Use spacing constant with fallback
  },
  // Example: If you needed specific padding for the escalated state icon
  escalatedIcon: {
    marginBottom: commonStyles.spacing?.medium || 16, // Use spacing constant with fallback
  }
});

// Define fallback colors in case commonStyles.COLORS is not fully loaded or defined
const FALLBACK_COLORS = {
  primary: '#007AFF',
  secondary: '#4CAF50',
  danger: '#e74c3c',
  warning: '#f39c12',
  textLight: '#FFFFFF',
};


export default function AlertScreen() {
  // Get navigation object using the hook (still needed for potential fallback navigation or other uses)
  const navigation = useNavigation();
  // State to track screen height for dynamic sizing (fallback/additional scaling)
  const [screenHeight, setScreenHeight] = useState(() => Dimensions.get('window').height);

  // Effect to update screen height when dimensions change
  useEffect(() => {
    const onChange = ({ window }) => {
      console.log("AlertScreen: Dimensions changed, new height:", window.height);
      setScreenHeight(window.height);
    };
    // Add event listener for dimension changes
    const subscription = Dimensions.addEventListener('change', onChange);
    // Cleanup function to remove the listener
    return () => {
      subscription?.remove(); // Use optional chaining in case subscription is null
    };
  }, []); // Empty dependency array means this runs only once on component mount

  // Get app context state and actions
  const appContext = useAppContext();
  // Destructure necessary values from appContext
  const {
    resolveFallAlert, // Action to resolve the initial fall alert
    dismissEscalatedAlert, // Action to dismiss the escalated alert state
    pendingFallData, // Data about the pending fall event
    isEscalated, // Boolean indicating if the alert has escalated
    escalationMessage, // Message shown in the escalated state
    // No need to destructure isFallAlertActive here, AppNavigator handles it
  } = appContext;

  // State to manage loading state for the "I'm Safe" button
  const [isResolving, setIsResolving] = useState(false);

  // Use colors from commonStyles.COLORS with fallback for robustness
  const COLORS = commonStyles.COLORS || FALLBACK_COLORS;

  // Determine the color for the activity indicator in the "I'm Safe" button
  // Use commonStyles.safeButtonText.color if available, otherwise use COLORS.textLight or fallback
  const safeButtonActivityIndicatorColor = (commonStyles?.safeButtonText?.color)
    ? commonStyles.safeButtonText.color
    : (COLORS.textLight || FALLBACK_COLORS.textLight);


  console.log(`--- AlertScreen Rendering --- isEscalated: ${isEscalated}, escalationMessage: "${escalationMessage}"`);
  console.log(`AlertScreen: Current screenHeight (state): ${screenHeight}`);

  // --- Handlers ---

  // Handler for the "I'm Safe" button
  const handleImSafe = async () => {
    if (isResolving) return; // Prevent multiple presses while resolving
    setIsResolving(true); // Show loading indicator
    try {
      // Ensure resolveFallAlert is a function before calling
      if (typeof resolveFallAlert === 'function') {
        await resolveFallAlert(); // Call the context action to resolve the alert
        // This action should set isFallAlertActive to false in AppContext,
        // which will automatically hide this screen via AppNavigator.
      } else {
        console.error("AlertScreen: resolveFallAlert is not a function in AppContext!");
        Alert.alert("Error", "Could not process 'I'm Safe' action. Please contact support.");
      }
    } catch (error) {
      console.error("AlertScreen: Error in handleImSafe:", error);
      Alert.alert("Error", "Failed to mark as safe. Please try again.");
    } finally {
      setIsResolving(false); // Hide loading indicator
    }
  };

  // Handler for the "Call Emergency" button
  const handleEmergencyContact = () => {
    // Replace '000' with the actual emergency contact number from settings or user data
    const emergencyNumber = '000'; // Example: Emergency services number
    Alert.alert(
        "Call Emergency Contact?",
        `This will attempt to call ${emergencyNumber}.`,
        [
            { text: "Cancel", style: "cancel" }, // Standard "Cancel" button
            {
                text: `Call ${emergencyNumber}`, // Button to initiate the call
                onPress: () => {
                    // Use Linking to open the phone dialer
                    let phoneNumber = Platform.OS === 'android' ? `tel:${emergencyNumber}` : `telprompt:${emergencyNumber}`;
                    Linking.openURL(phoneNumber)
                        .catch(err => Alert.alert("Failed to Call", "Could not open the phone app. Please call manually."));
                }
            }
        ]
    );
    // You might choose to dismiss the alert screen after initiating the call,
    // depending on your desired user flow.
    // if (typeof resolveFallAlert === 'function') {
    //   resolveFallAlert(); // Dismiss the alert state in context
    // }
  };

  // Handler for the "Go to Home Page" button in the escalated state
  const handleGoToHome = () => {
    console.log("AlertScreen: 'Go to Home Page' pressed.");
    // Ensure dismissEscalatedAlert is a function before calling
    if (typeof dismissEscalatedAlert === 'function') {
      dismissEscalatedAlert(); // Call the context action to dismiss the escalated state
      // REMOVED: navigation.navigate('Home'); // <-- REMOVED THIS LINE
      // Dismissing the escalated alert state (setting isFallAlertActive to false in AppContext)
      // will automatically hide this screen via AppNavigator and return to the previous screen (Home).
    } else {
      console.error("AlertScreen: dismissEscalatedAlert is not a function in AppContext! Cannot dismiss alert state.");
      Alert.alert("Error", "Could not dismiss the alert. Please restart the app or contact support.");
      // As a last resort fallback, you could try navigating, but it's not the intended flow
      // navigation.navigate('Home');
    }
  };

  // Dynamic sizes based on local screenHeight state (fallback/additional scaling)
  const dynamicIconSize = screenHeight * 0.12;
  // Use typography sizes from commonStyles as primary, but allow dynamic scaling if needed
  const dynamicEscalatedTitleFontSize = commonStyles.typography?.fontSizeTitle || (screenHeight * 0.035);
  const dynamicEscalatedMessageFontSize = commonStyles.typography?.fontSizeBase || (screenHeight * 0.025);
  const dynamicEscalatedMessageLineHeight = commonStyles.typography?.lineHeightBase || (screenHeight * 0.03); // Assume a base line height in typography
  const dynamicEscalatedItalicMessageFontSize = commonStyles.typography?.fontSizeSmall || (screenHeight * 0.02);


  // --- Render different UI based on escalation state ---

  if (isEscalated) {
    return (
      // Apply the escalated container style from commonStyles
      <View style={commonStyles.alertScreenEscalatedContainer || { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: 'green', padding: 30 }}>
        <Ionicons
          name="checkmark-circle-outline"
          size={dynamicIconSize} // Use dynamic size
          color={COLORS.cardBackground} // Use secondary color from commonStyles.COLORS
          style={localStyles.escalatedIcon} // Apply local style for icon spacing
        />
        <Text style={[commonStyles.alertScreenEscalatedTitle || { fontSize: 26, fontWeight: 'bold', color: 'white', textAlign: 'center' }, { fontSize: dynamicEscalatedTitleFontSize, marginTop: commonStyles.spacing?.medium || 16 }]}> 
          Carer Notified
        </Text>
        <Text style={[commonStyles.alertScreenEscalatedMessage || { fontSize: 18, color: 'white', textAlign: 'center', lineHeight: 24 }, { fontSize: dynamicEscalatedMessageFontSize, lineHeight: dynamicEscalatedMessageLineHeight, marginVertical: commonStyles.spacing?.medium || 16 }]}> 
          {escalationMessage || "Your carer has been informed and help is on the way."}
        </Text>
        <Text style={[commonStyles.alertScreenEscalatedItalicMessage || { fontSize: 16, color: 'white', textAlign: 'center', fontStyle: 'italic', lineHeight: 22 }, { fontSize: dynamicEscalatedItalicMessageFontSize, lineHeight: dynamicEscalatedMessageLineHeight, marginBottom: commonStyles.spacing?.large || 24 }]}>
          You can now return to the home screen or wait for assistance.
        </Text>
        <TouchableOpacity
          // Combine base button style, alert screen specific button style, and color/margin overrides
          style={[
            commonStyles.button || { backgroundColor: '#007bff', paddingVertical: 15, borderRadius: 8, alignItems: 'center', justifyContent: 'center' }, // Base button style with fallback
            commonStyles.alertScreenButton || { minWidth: '80%' }, // Alert screen specific button style with fallback
            { backgroundColor: COLORS.primary, marginTop: commonStyles.spacing?.large || 24 } // Use primary color and large top margin, use spacing constant with fallback
          ]}
          onPress={handleGoToHome} // Handler to dismiss alert state
        >
          <Text style={commonStyles.buttonText || { color: 'white', fontSize: 18, fontWeight: '600' }}>Go to Home Page</Text>
        </TouchableOpacity>
      </View>
    );
  }

  // Default Alert Screen UI (before escalation)
  return (
    // Apply the main alert container style from commonStyles
    <View style={commonStyles.alertContainer || { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: 'red', padding: 20 }}>
       <Ionicons
        name="warning-outline"
        size={dynamicIconSize}
        color={COLORS.textLight} // Use light text color for visibility on red background
        style={localStyles.warningIcon} // Apply local style for icon spacing
      />
      <Text style={commonStyles.alertTitle || { fontSize: 30, fontWeight: 'bold', color: 'white', textAlign: 'center' }}>FALL DETECTED!</Text>
      <Text style={commonStyles.alertMessage || { fontSize: 20, color: 'white', textAlign: 'center', marginBottom: 30 }}>
        A potential fall has been detected. Please respond within 30 seconds.
        {pendingFallData?.detected_at ? `\n(Detected: ${new Date(pendingFallData.detected_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})})` : ''}
      </Text>
      <TouchableOpacity
        // Apply common safe button style from commonStyles
        style={commonStyles.safeButton || { backgroundColor: 'green', padding: 15, borderRadius: 5, marginBottom: 15, alignItems: 'center' }}
        onPress={handleImSafe} // Handler to resolve the alert
        disabled={isResolving} // Disable button while resolving
      >
        {isResolving ? (
            // Use the activity indicator color determined earlier
            <ActivityIndicator color={safeButtonActivityIndicatorColor} />
          ) : (
            // Apply common safe button text style from commonStyles
            <Text style={commonStyles.safeButtonText || { color: 'white', fontSize: 18, fontWeight: 'bold' }}>I'm Safe</Text>
        )}
      </TouchableOpacity>
      <TouchableOpacity
        // Apply common emergency button style from commonStyles
        style={commonStyles.emergencyButton || { backgroundColor: 'orange', padding: 15, borderRadius: 5, alignItems: 'center' }} 
        onPress={handleEmergencyContact} // Handler to initiate emergency call
      >
        <Text style={commonStyles.emergencyButtonText || { color: 'white', fontSize: 18, fontWeight: 'bold' }}>Call Emergency</Text> 
      </TouchableOpacity>
    </View>
  );
}