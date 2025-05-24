import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Device from 'expo-device'; // Ensure Device is imported
import { createContext, useCallback, useContext, useEffect, useMemo, useReducer } from 'react';
import { Alert } from 'react-native';
import apiService from '../api/apiService';
import {
  USER_DATA_KEY,
  USER_TOKEN_KEY
} from '../constants/appConstants'; // Ensure this path is correct

const AuthContext = createContext(null);

// Reducer function to manage authentication state changes.
const authReducer = (prevState, action) => {
  console.log("AuthReducer Action:", action.type, "Payload:", action.payload);
  switch (action.type) {
    case 'SET_LOADING':
      return { ...prevState, isLoading: action.payload.isLoading };
    case 'RESTORE_TOKEN':
      return { ...prevState, userToken: action.payload.token, userData: action.payload.userData, isLoading: false };
    case 'SIGN_IN':
      return { ...prevState, userToken: action.payload.token, userData: action.payload.userData, isLoading: false, error: null };
    case 'SIGN_OUT':
      return { ...prevState, userToken: null, userData: null, isLoading: false, error: null };
    case 'SET_USER_DATA':
      return { ...prevState, userData: action.payload.userData, isLoading: false }; 
    case 'SET_ERROR':
      return { ...prevState, error: action.payload.error, isLoading: false };
    default:
      return prevState;
  }
};

export const AuthProvider = ({ children }) => {
  const [authState, dispatch] = useReducer(authReducer, {
    isLoading: true,
    userToken: null,
    userData: null,
    error: null,
  });

  useEffect(() => {
    const bootstrapAsync = async () => {
      console.log("AuthContext: Bootstrapping - restoring token/data");
      let userToken = null;
      let userData = null;
      try {
        userToken = await AsyncStorage.getItem(USER_TOKEN_KEY);
        const userDataString = await AsyncStorage.getItem(USER_DATA_KEY);
        if (userDataString) {
          userData = JSON.parse(userDataString);
        }
        console.log("AuthContext: Restored from storage - Token:", userToken);
        console.log("AuthContext: Restored from storage - UserData:", JSON.stringify(userData, null, 2));
      } catch (e) {
        console.error('AuthContext: Restoring token/data failed', e);
      }
      dispatch({ type: 'RESTORE_TOKEN', payload: { token: userToken, userData } });
    };
    bootstrapAsync();
  }, []);

  const signIn = useCallback(async (email, password) => {
    dispatch({ type: 'SET_LOADING', payload: { isLoading: true } }); 
    try {
      const device_name = `${Device.osName} - ${Device.modelName || 'Device'}`;
      console.log('AuthContext: Attempting Sign In with payload:', { email, password_length: password?.length, device_name });
      const response = await apiService.loginElderly({ email, password, device_name });
      console.log('AuthContext: Login API Full Response:', JSON.stringify(response, null, 2));
      
      const token = response.token || response.data?.token;
      let fetchedUserData = response.user || response.data?.user; // Data from login response

      if (!token) {
        const serverMessage = response.message || response.data?.message || "Login failed: No token received from server.";
        console.error("AuthContext: No token received from login API. Response was:", response);
        throw new Error(serverMessage);
      }
      console.log("AuthContext: Token received from login API:", token);
      await AsyncStorage.setItem(USER_TOKEN_KEY, token);

      // If userData is not directly in login response, or if you always want to fetch fresh user details:
      if (!fetchedUserData && token) { // Or just: if (token) { ... } to always fetch
        console.log("AuthContext: User data not in login response (or fetching fresh). Fetching with getElderlyUser, token:", token);
        const userResponse = await apiService.getElderlyUser(token); // Assumes this fetches the logged-in user's details
        console.log('AuthContext: Get Elderly User API Full Response:', JSON.stringify(userResponse, null, 2));
        fetchedUserData = userResponse.data || userResponse; // Adjust based on your API structure
      }
      
      if (fetchedUserData) {
        console.log("AuthContext: ==> FINAL UserData being processed for AuthContext state:", JSON.stringify(fetchedUserData, null, 2));
        // Log the specific ID fields you expect to use for elderly_id
        console.log("AuthContext: UserData.id from fetched data:", fetchedUserData.id);
        console.log("AuthContext: UserData.profile_id from fetched data (if exists):", fetchedUserData.profile_id);
        
        await AsyncStorage.setItem(USER_DATA_KEY, JSON.stringify(fetchedUserData));
        dispatch({ type: 'SIGN_IN', payload: { token, userData: fetchedUserData } }); 
        console.log('AuthContext: Sign In successful. AuthState updated with the above userData.');
      } else {
        console.error("AuthContext: CRITICAL - User data is NULL or UNDEFINED after login attempt and/or fetch.");
        dispatch({ type: 'SIGN_IN', payload: { token, userData: null } }); 
        throw new Error("Login successful, but user data could not be retrieved.");
      }
    } catch (error) {
      const errorMessage = error.message || 'An unexpected error occurred during login.';
      console.error('AuthContext: Sign In Error Catch Block -', errorMessage, error.response?.data || error);
      dispatch({ type: 'SET_ERROR', payload: { error: errorMessage } }); 
      Alert.alert('Login Failed', errorMessage);
      throw error; 
    }
  }, [dispatch]);

  const signOut = useCallback(async () => {
    // ... (signOut logic remains the same) ...
    const currentToken = authState.userToken; 
    dispatch({ type: 'SET_LOADING', payload: { isLoading: true } });
    try {
      if (currentToken) {
        console.log('AuthContext: Signing out - calling API if configured');
        // await apiService.logoutElderly(currentToken);
      }
    } catch (e) {
      console.error('AuthContext: Logout API call failed', e);
    } finally {
      console.log('AuthContext: Clearing local token/data and signing out.');
      await AsyncStorage.removeItem(USER_TOKEN_KEY);
      await AsyncStorage.removeItem(USER_DATA_KEY);
      dispatch({ type: 'SIGN_OUT' }); 
    }
  }, [dispatch, authState.userToken]);

  const signUp = useCallback(async (name, email, password, password_confirmation) => {
    // ... (signUp logic remains the same, ensuring it does NOT auto-login) ...
    dispatch({ type: 'SET_LOADING', payload: { isLoading: true } });
    try {
      const device_name = `${Device.osName} - ${Device.modelName || 'Device'}`;
      const trimmedName = name.trim();
      const nameParts = trimmedName.split(/\s+/);
      const first_name = nameParts[0] || '';
      const last_name = nameParts.slice(1).join(' ') || '';
      const registrationPayload = {
        name: trimmedName, 
        first_name: first_name,
        last_name: last_name,
        email: email.trim(),
        password: password,
        password_confirmation: password_confirmation,
        device_name: device_name,
        date_of_birth: "1940-01-01", 
        gender: "male",      
        primary_phone: "+1234567890",  
        current_address: "123 Main St", 
        emergency_contact_name: "Jane Doe", 
        emergency_contact_phone: "+1987654321", 
        emergency_contact_relationship: "Daughter", 
        mobility_status: "wheelchair_bound",   
        vision_status: "normal",     
        hearing_status: "normal",    
        care_level: "basic",          
        preferred_language: "English",
        device_status: "active",      
        living_situation: "with_family",  
        activity_level: "active"     
      };
      console.log('AuthContext: Signing up with payload (VERIFY PLACEHOLDERS):', JSON.stringify(registrationPayload, null, 2));
      const response = await apiService.registerElderly(registrationPayload);
      console.log('AuthContext: Registration API successful:', response.message || "Registration successful.");
      Alert.alert('Registration Successful', response.message || 'Please log in with your new credentials.');
      dispatch({ type: 'SET_LOADING', payload: { isLoading: false } });
    } catch (error) { 
      const errorMessage = error.response?.data?.message || error.message || 'An unexpected error occurred during registration.';
      console.error('AuthContext: Sign Up Error -', errorMessage, error.response?.data?.errors);
      dispatch({ type: 'SET_ERROR', payload: { error: errorMessage } });
      Alert.alert('Registration Failed', errorMessage);
      throw error;
    }
  }, [dispatch]);

  const fetchAndUpdateUser = useCallback(async () => {
    // ... (fetchAndUpdateUser logic remains the same) ...
    if (authState.userToken) { 
      console.log("AuthContext: fetchAndUpdateUser - Token exists, proceeding.");
      try {
        let updatedUserData = await apiService.getElderlyUser(authState.userToken);
        updatedUserData = updatedUserData.data || updatedUserData; 
        console.log("AuthContext: fetchAndUpdateUser - Fetched data:", JSON.stringify(updatedUserData, null, 2));
        await AsyncStorage.setItem(USER_DATA_KEY, JSON.stringify(updatedUserData));
        dispatch({ type: 'SET_USER_DATA', payload: { userData: updatedUserData } }); 
        console.log("AuthContext: User data updated via fetchAndUpdateUser.");
      } catch (error) {
        const errorMessage = error.message || "Failed to fetch/update user data";
        console.error("AuthContext: Failed to fetch/update user data", error);
        dispatch({ type: 'SET_ERROR', payload: { error: errorMessage } });  
        if (error.message.includes("Unauthenticated") || error.status === 401 || error.response?.status === 401) {
          console.warn("AuthContext: Unauthenticated error during fetchAndUpdateUser. Signing out.");
          await AsyncStorage.removeItem(USER_TOKEN_KEY);
          await AsyncStorage.removeItem(USER_DATA_KEY);
          dispatch({ type: 'SIGN_OUT' }); 
        }
      }
    } else {
      console.log("AuthContext: fetchAndUpdateUser - No token, skipping.");
    }
  }, [dispatch, authState.userToken]);

  const authContextValue = useMemo(() => ({
    ...authState,
    signIn,
    signOut,
    signUp,
    fetchAndUpdateUser,
    clearError: () => dispatch({ type: 'SET_ERROR', payload: { error: null } }),
  }), [authState, signIn, signOut, signUp, fetchAndUpdateUser]);

  return <AuthContext.Provider value={authContextValue}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined || context === null) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};