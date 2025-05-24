import { createStackNavigator } from '@react-navigation/stack';
import { ActivityIndicator, Text, View } from 'react-native'; // Import Text for loading message

// Import Screens
import AlertScreen from '../screens/AlertScreen';
import HomeScreen from '../screens/HomeScreen';
import LoginScreen from '../screens/LoginScreen';
import RegisterScreen from '../screens/RegisterScreen';
import SettingsScreen from '../screens/SettingsScreen';

// Import contexts to check authentication state and alert state
import { useAppContext } from '../contexts/AppContext'; // Assuming you use AppContext for global alert
import { useAuth } from '../contexts/AuthContext'; // Assuming you use AuthContext

// Import the common styles, which includes COLORS, typography, and spacing
import { commonStyles } from '../styles/commonStyles';

const Stack = createStackNavigator();

export default function AppNavigator() {
  console.log("--- AppNavigator rendering ---", new Date().toLocaleTimeString());
  // Get authentication state and loading status from AuthContext
  // Assuming useAuth provides userToken and isLoading
  const { userToken, isLoading: authIsLoading } = useAuth();
  // Get fall alert state from AppContext to control global alert screen
  // Assuming useAppContext provides isFallAlertActive
  const { isFallAlertActive } = useAppContext();

  console.log(`AppNavigator State: authIsLoading=${authIsLoading}, userToken=${userToken ? 'Exists' : 'Null'}, isFallAlertActive=${isFallAlertActive}`);


  // Show a loading indicator screen while authentication status is being determined.
  // This is crucial to prevent rendering the wrong stack before the token is restored.
  if (authIsLoading) {
    console.log("AppNavigator: Showing Loading Screen...");
    return (
      // Apply the centeredView style for layout from commonStyles
      <View style={commonStyles.centeredView}>
        {/* Apply ActivityIndicator size and use a color from commonStyles.COLORS */}
        <ActivityIndicator size="large" color={commonStyles.COLORS.primary} />
        {/* Optionally add a loading text */}
        <Text style={[commonStyles.cardText, { marginTop: commonStyles.spacing.medium, color: commonStyles.COLORS.textSecondary }]}>Loading...</Text>
      </View>
    );
  }

  // If a fall alert is active, prioritize showing the AlertScreen globally.
  // This stack is rendered instead of the main app stack when isFallAlertActive is true.
  if (isFallAlertActive) {
    console.log("AppNavigator: Showing Global Alert Screen...");
    return (
      // A simple stack navigator just for the alert screen
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        <Stack.Screen
          name="GlobalAlert" // A unique name for the alert screen within this temporary stack
          component={AlertScreen}
          options={{ headerShown: false }} // Alert screen typically has no header
        />
      </Stack.Navigator>
    );
  }

  // Main navigation logic based on authentication state (userToken presence).
  // If userToken is null, show authentication screens; otherwise, show main app screens.
  console.log(`AppNavigator: Rendering ${userToken == null ? 'Auth' : 'App'} Stack`);
  return (
    // The NavigationContainer is typically at the root (in App.js) wrapping this navigator.
    // Set initial route based on whether a user is logged in
    <Stack.Navigator
      initialRouteName={userToken == null ? "Login" : "Home"} // Ensure initial route matches state
      screenOptions={{
        // Default screen options applied to all screens in this navigator
        headerStyle: {
          // Use the subtle header background color from commonStyles.COLORS
          backgroundColor: commonStyles.COLORS.headerBackground // <-- Changed color here
        },
        // Use the primary text color from commonStyles.COLORS for header text and icons
        headerTintColor: commonStyles.COLORS.textPrimary, // <-- Changed text color for better contrast on light background
        headerTitleStyle: {
          fontWeight: commonStyles.typography.fontWeightBold, // Make header titles bold using typography style
        },
        // Set the background color for screen transitions using a common background color
        cardStyle: { backgroundColor: commonStyles.COLORS.lightBackground } // Use background color from commonStyles
      }}
    >
      {userToken == null ? (
        // User is NOT signed in: Show Authentication screens (Login, Register)
        <>
          <Stack.Screen
            name="Login"
            component={LoginScreen}
            options={{ headerShown: false }} // Hide header for the Login screen
          />
          {/* Include Register screen */}
          <Stack.Screen
            name="Register"
            component={RegisterScreen}
            options={{ headerShown: false }} // Hide header for the Register screen
          />
        </>
      ) : (
        // User IS signed in: Show Main App screens (Home, Settings)
        <>
          <Stack.Screen
            name="Home"
            component={HomeScreen}
            options={{ title: 'Dashboard' }} // Set the title for the Home screen header
          />
          <Stack.Screen
            name="Settings"
            component={SettingsScreen}
            options={{ title: 'Settings' }} // Set the title for the Settings screen header
          />
          {/* Add other authenticated screens here as needed */}
        </>
      )}
    </Stack.Navigator>
  );
}