import React from 'react';
import { ScrollView, View, Text, TouchableOpacity, ActivityIndicator, Button, Alert, Linking, Platform } from 'react-native';
import { useAuth } from '../contexts/AuthContext';
import { useAppContext } from '../contexts/AppContext';
import { commonStyles } from '../styles/commonStyles';
import Ionicons from '@expo/vector-icons/Ionicons';
import * as Application from 'expo-application'; // To get app version

export default function SettingsScreen() {
  const { signOut, userData, isLoading: authLoading } = useAuth();
  const { systemStatus } = useAppContext();

  const appVersion = Application.nativeApplicationVersion || 'N/A';
  const appBuildVersion = Application.nativeBuildVersion || 'N/A';


  const handleOpenAppSettings = () => {
    if (Platform.OS === 'ios') {
      Linking.openURL('app-settings:');
    } else {
      Linking.openSettings(); // For Android, opens general app settings screen
    }
  };

  return (
    <ScrollView 
        style={commonStyles.scrollViewContainer}
        contentContainerStyle={commonStyles.contentContainer}
    >
      <Text style={commonStyles.header}>Settings & Configuration</Text>

      {/* Account Section */}
      <View style={commonStyles.infoCard}>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
            <Text style={commonStyles.cardTitle}>Account</Text>
            <Ionicons name="person-circle-outline" size={24} color={commonStyles.iconStyle.color} />
        </View>
        <Text style={commonStyles.cardText}>Logged in as: {userData?.name || userData?.email || 'N/A'}</Text>
        <TouchableOpacity
          style={[commonStyles.button, { backgroundColor: commonStyles.dangerButton.backgroundColor, marginTop: 15 }]}
          onPress={signOut}
          disabled={authLoading}
        >
          {authLoading ? (
            <ActivityIndicator color={commonStyles.buttonText.color} /> 
            ) : (
            <View style={{flexDirection: 'row', alignItems: 'center'}}>
                <Ionicons name="log-out-outline" size={20} color={commonStyles.buttonText.color} style={{marginRight: 8}}/>
                <Text style={commonStyles.buttonText}>Logout</Text>
            </View>
            )
          }
        </TouchableOpacity>
      </View>
      
      {/* System Information Section */}
      <View style={commonStyles.infoCard}>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
            <Text style={commonStyles.cardTitle}>System Information</Text>
            <Ionicons name="information-circle-outline" size={24} color={commonStyles.iconStyle.color} />
        </View>
        <Text style={commonStyles.cardText}>Overall Status: <Text style={{color: systemStatus.color, fontWeight: '500'}}>{systemStatus.message}</Text></Text>
        <Text style={commonStyles.cardText}>Fall Detection Model: v1.0 (API Driven)</Text>
        <Text style={commonStyles.cardText}>App Version: {appVersion} (Build: {appBuildVersion})</Text>
        {/* Placeholder for model update settings */}
        <Button 
            title="Check for App Updates (Not Implemented)" 
            onPress={() => Alert.alert("TODO", "App update check feature is not implemented yet.")} 
            color={commonStyles.button.backgroundColor}
        />
      </View>

      {/* Notification & Permissions Section */}
      <View style={commonStyles.infoCard}>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
            <Text style={commonStyles.cardTitle}>Notifications & Permissions</Text>
            <Ionicons name="notifications-outline" size={24} color={commonStyles.iconStyle.color} />
        </View>
        <Text style={commonStyles.cardText}>Push Notifications: Enabled (Simulated)</Text>
        <Text style={commonStyles.cardText}>Sound Alerts: Enabled</Text>
        <View style={{marginTop: 10}}>
            <Button 
                title="Open App Settings" 
                onPress={handleOpenAppSettings}
                color={commonStyles.button.backgroundColor}
            />
        </View>
      </View>

      {/* User Preferences Section */}
      <View style={commonStyles.infoCard}>
        <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }}>
            <Text style={commonStyles.cardTitle}>User Preferences</Text>
            <Ionicons name="options-outline" size={24} color={commonStyles.iconStyle.color} />
        </View>
        <Text style={commonStyles.cardText}>Emergency Contact: Not Set (Feature TODO)</Text>
        <Button 
            title="Edit Preferences (Not Implemented)" 
            onPress={() => Alert.alert("TODO", "User preference editing feature is not implemented yet.")} 
            color={commonStyles.button.backgroundColor}
        />
      </View>
    </ScrollView>
  );
}