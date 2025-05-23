import { Accelerometer, Gyroscope } from 'expo-sensors';
import { Alert, Platform } from 'react-native';

/**
 * Requests permissions for Accelerometer and Gyroscope.
 * @returns {Promise<object>} An object indicating whether permissions were granted.
 * e.g., { accelerometer: true, gyroscope: false }
 */
export const requestSensorPermissions = async () => {
  console.log("PermissionService: Requesting sensor permissions...");
  try {
    // For Accelerometer and Gyroscope, requestPermissionsAsync() might not always be necessary
    // on Android as they are often considered "normal" permissions granted at install.
    // However, it's good practice for consistency and for iOS where explicit permission is needed.
    // Ensure you have NSMotionUsageDescription in app.json for iOS.

    const { status: accelStatus } = await Accelerometer.requestPermissionsAsync();
    const { status: gyroStatus } = await Gyroscope.requestPermissionsAsync();
    
    const permissionsGranted = {
      accelerometer: accelStatus === 'granted',
      gyroscope: gyroStatus === 'granted',
    };
    console.log("PermissionService: Permissions status -", permissionsGranted);

    if (!permissionsGranted.accelerometer || !permissionsGranted.gyroscope) {
      Alert.alert(
        "Permissions Required",
        "Accelerometer and Gyroscope permissions are needed for fall detection features. Please enable them in your app settings if you denied them. Some functionalities might be limited.",
        [{ text: "OK" }]
      );
    }
    return permissionsGranted;
  } catch (error) {
    console.error("PermissionService: Error requesting sensor permissions:", error);
    Alert.alert("Permission Error", "Could not request sensor permissions. Please check your app settings.");
    return { accelerometer: false, gyroscope: false }; // Return a default denied state on error
  }
};