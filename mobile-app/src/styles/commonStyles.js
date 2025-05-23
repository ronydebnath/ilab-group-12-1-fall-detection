import { StyleSheet, Platform } from 'react-native';

// --- Color Palette (Production Aesthetic) ---
// Using a calming, clean palette with medical undertones
const COLORS = {
  primary: '#007BFF',       // A standard blue for interactive elements
  primaryDark: '#0056b3',
  secondary: '#4CAF50',     // Green for success/safe states
  danger: '#BF3131',        // A softer red for alerts, errors
  warning: '#FFB300',        // Amber for warnings
  lightBackground: '#F5F7FB', // Very light blue-grey background
  cardBackground: '#FFFFFF', // Pure white for cards
  textPrimary: '#333333',   // Dark grey for primary text
  textSecondary: '#757575', // Medium grey for secondary text
  textLight: '#FFFFFF',     // White text for dark backgrounds
  borderColor: '#EEEEEE',    // Very light grey for borders/dividers
  iconColor: '#757575',     // Medium grey for icons
  headerBackground: '#FAF6E9', // Light background for headers
  loginButton: '#48A6A7', 
  // Refined light colors for cards based on image aesthetic
  cardGreen: '#E8F5E9', // Very light green
  cardBlue: '#E3F2FD',  // Very light blue
  cardYellow: '#FFFDE7', // Very light yellow
  cardRed: '#FFEBEE',   // Very light red
  cardPurple: '#EDE7F6', // Very light purple (Adding another option)
};

// --- Typography (Production Aesthetic) ---
// Defining clear, readable font sizes and weights
const typography = {
  fontSizeBase: 15, // Slightly smaller base
  fontSizeSmall: 13,
  fontSizeMedium: 17, // For card titles
  fontSizeLarge: 20, // For headers/prominent text
  fontSizeTitle: 26, // For main screen titles/greetings
  fontSizeAlertTitle: Platform.OS === 'ios' ? 36 : 32, // Dynamic based on platform
  fontWeightLight: '300',
  fontWeightNormal: '400',
  fontWeightMedium: '500',
  fontWeightSemiBold: '600',
  fontWeightBold: '700',
  lineHeightBase: 22, // Adjusted line height
};

// --- Spacing (Production Aesthetic) ---
// Consistent spacing scale
const spacing = {
  xSmall: 4,
  small: 8,
  medium: 16,
  large: 24,
  xLarge: 32,
  xxLarge: 40,
};


export const commonStyles = StyleSheet.create({
  // **IMPORTANT:** Include the helper objects as properties of the exported commonStyles object
  COLORS: COLORS, // Make the COLORS object accessible via commonStyles.COLORS
  typography: typography, // Make the typography object accessible via commonStyles.typography
  spacing: spacing, // Make the spacing object accessible via commonStyles.spacing

  // --- Containers & Layout ---
  container: { // For general screens like Login, Register
    flex: 1,
    // Removed justifyContent: 'center' for general container, better handled in contentContainer
    paddingHorizontal: spacing.medium,
    paddingVertical: spacing.large,
    backgroundColor: COLORS.headerBackground,
  },
  scrollViewContainer: { // For scrollable content like Home, Settings
    flex: 1,
    backgroundColor: COLORS.lightBackground,
  },
  contentContainer: { // Padding for content within ScrollView
    padding: spacing.medium,
    paddingBottom: spacing.xxLarge, // More padding at the bottom
  },
  centeredView: { // For loading screens or centering content
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.lightBackground,
  },

  // --- Typography Styles ---
  title: { // For main screen titles (Login, Register)
    fontSize: typography.fontSizeTitle,
    fontWeight: typography.fontWeightBold,
    color: COLORS.textPrimary,
    marginBottom: spacing.xLarge, // Increased space below title
    textAlign: 'center',
  },
  header: { // For section headers on Home/Settings screens
    fontSize: typography.fontSizeLarge,
    fontWeight: typography.fontWeightSemiBold, // Semi-bold for headers
    color: COLORS.textPrimary,
    marginBottom: spacing.medium,
    marginTop: spacing.medium, // Consistent spacing
  },
   cardTitle: { // Title within smaller info cards
    fontSize: typography.fontSizeMedium,
    fontWeight: typography.fontWeightSemiBold,
    color: COLORS.textPrimary,
    marginBottom: spacing.small,
  },
  cardText: { // Standard text within cards
    fontSize: typography.fontSizeBase,
    color: COLORS.textSecondary,
    lineHeight: typography.lineHeightBase,
  },
  statusText: { // For system status messages
    fontSize: typography.fontSizeBase,
    fontWeight: typography.fontWeightMedium, // Medium weight
    marginLeft: spacing.small,
  },
  linkText: { // For navigation links (e.g., "Don't have an account?")
    color: COLORS.primary,
    textAlign: 'center',
    marginTop: spacing.medium,
    fontSize: typography.fontSizeBase,
    fontWeight: typography.fontWeightMedium,
  },
  errorText: { // For displaying errors on forms
    color: COLORS.danger,
    marginBottom: spacing.medium,
    textAlign: 'center',
    fontSize: typography.fontSizeSmall,
  },
  buttonText: { // Base text style for buttons
    color: COLORS.textLight,
    fontSize: typography.fontSizeMedium,
    fontWeight: typography.fontWeightSemiBold,
  },

  // --- Input Fields ---
  input: {
    height: 50,
    borderColor: COLORS.borderColor,
    borderWidth: 1,
    marginBottom: spacing.medium,
    paddingHorizontal: spacing.medium,
    borderRadius: 8,
    backgroundColor: COLORS.cardBackground,
    fontSize: typography.fontSizeBase,
    color: COLORS.textPrimary,
    // Subtle shadow for input fields
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 1,
    elevation: 1,
  },

  // --- Buttons ---
  button: { // Default button style
    backgroundColor: COLORS.loginButton,
    paddingVertical: spacing.medium, // Standard vertical padding
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.medium,
    minHeight: 50,
    // Refined shadow for buttons
    shadowColor: COLORS.primary, // Shadow color matching button
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 5,
    elevation: 6,
  },
  buttonDisabled: {
    backgroundColor: COLORS.textSecondary, // Greyed out for disabled state
    shadowOpacity: 0.1,
    elevation: 2,
  },
   warningButton: { // Warning button style
    backgroundColor: COLORS.warning,
    marginTop: spacing.medium,
  },
  dangerButton: { // Danger button style
    backgroundColor: COLORS.danger,
    marginTop: spacing.medium,
  },


  // --- Cards (Production Aesthetic) ---
  infoCard: { // General style for smaller info cards (used on Settings, etc.)
    backgroundColor: COLORS.cardBackground,
    borderRadius: 12,
    padding: spacing.medium,
    marginBottom: spacing.medium,
    // Refined shadow - subtle and clean
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 6,
  },
  // --- Home Screen Specific Styles ---
  homeUserProfileCard: { // Style for the larger user profile card on Home screen
    backgroundColor: COLORS.cardBackground, // Default to white, color added in HomeScreen
    borderRadius: 16, // More rounded
    padding: spacing.large,
    marginBottom: spacing.large,
    // Refined shadow for prominence
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.15,
    shadowRadius: 15,
    elevation: 10,
  },
  homeUserProfileTitle: { // Title within the user profile card
    fontSize: typography.fontSizeLarge,
    fontWeight: typography.fontWeightBold,
    color: COLORS.textPrimary,
    marginBottom: spacing.small,
  },
  userProfileText: { // Text within the user profile card
    fontSize: typography.fontSizeBase,
    color: COLORS.textSecondary,
    marginBottom: spacing.xSmall,
    lineHeight: typography.lineHeightBase, // Ensure consistent line height
  },
  homeInfoCard: { // Style for the smaller info cards on Home screen
     backgroundColor: COLORS.cardBackground, // Default to white, color added in HomeScreen
    borderRadius: 12, // Standard border radius
    padding: spacing.medium,
    marginBottom: spacing.medium,
    // Refined shadow - subtle and clean
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.15,
    shadowRadius: 10,
    elevation: 8,
  },
  homeActionsContainer: { // Container for action buttons at the bottom
    marginTop: spacing.large,
  },
   // Style for the greeting text
  greetingText: {
    fontSize: typography.fontSizeTitle, // Use title font size
    fontWeight: typography.fontWeightBold, // Bold
    color: COLORS.textPrimary, // Primary text color
    marginBottom: spacing.large, // Space below greeting
  },


  // --- Alert Screen Specific Styles ---
  alertContainer: { // Initial red alert screen
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.danger,
    padding: spacing.large,
  },
  alertTitle: { // Title on the alert screen
    fontSize: typography.fontSizeAlertTitle,
    fontWeight: typography.fontWeightBold,
    color: COLORS.textLight,
    textAlign: 'center',
    marginBottom: spacing.large,
  },
  alertMessage: { // Message on the alert screen
    fontSize: typography.fontSizeLarge,
    color: COLORS.textLight,
    textAlign: 'center',
    marginBottom: spacing.xLarge,
    lineHeight: typography.lineHeightBase,
  },
  safeButton: { // "I'm Safe" button
    backgroundColor: COLORS.secondary, // Green
    paddingVertical: spacing.large,
    paddingHorizontal: spacing.xLarge,
    borderRadius: 10,
    marginBottom: spacing.medium,
    minWidth: '70%',
    alignItems: 'center',
    shadowColor: COLORS.secondary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 5,
    elevation: 7,
  },
  safeButtonText: {
    color: COLORS.textLight,
    fontSize: typography.fontSizeLarge,
    fontWeight: typography.fontWeightBold,
  },
  emergencyButton: { // "Call Emergency" button
    backgroundColor: COLORS.warning, // Orange
    paddingVertical: spacing.medium,
    paddingHorizontal: spacing.large,
    borderRadius: 10,
    minWidth: '60%',
    alignItems: 'center',
    shadowColor: COLORS.warning,
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 5,
  },
  emergencyButtonText: {
    color: COLORS.textLight,
    fontSize: typography.fontSizeMedium,
    fontWeight: typography.fontWeightBold,
  },
  alertScreenEscalatedContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.secondary, // Can change this to a success color if escalated means help is confirmed
    padding: spacing.large,
  },
  alertScreenEscalatedTitle: {
    fontSize: typography.fontSizeTitle,
    fontWeight: typography.fontWeightBold,
    color: COLORS.textLight,
    textAlign: 'center',
    marginBottom: spacing.medium,
    marginTop: spacing.medium,
  },
  alertScreenEscalatedMessage: {
    fontSize: typography.fontSizeMedium,
    color: COLORS.textLight,
    textAlign: 'center',
    marginBottom: spacing.medium,
    lineHeight: typography.lineHeightBase,
  },
  alertScreenEscalatedItalicMessage: {
    fontSize: typography.fontSizeBase,
    color: COLORS.textLight,
    textAlign: 'center',
    fontStyle: 'italic',
    marginBottom: spacing.large,
    lineHeight: typography.lineHeightBase,
  },
  alertScreenButton: { // General properties for buttons on alert screen (e.g., Go to Home)
    minWidth: '80%',
    paddingVertical: spacing.medium,
    // Color and text style will be overridden by specific button styles or inline styles
  },
   alertScreenButtonText: { // Base text for alert screen buttons
    fontSize: typography.fontSizeMedium,
    fontWeight: typography.fontWeightSemiBold,
    // color will be overridden
  },


  // --- Utility ---
  centeredView: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.lightBackground,
  },
  activityIndicator: {
    marginTop: spacing.medium,
  },
  iconStyle: { // Default icon style
    marginRight: spacing.small,
    color: COLORS.iconColor,
  },
   rowSpaceBetween: { // Utility for row layout with space between items
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },

  // --- Login Screen Specific Styles ---
  loginIconContainer: { // Renamed from iconContainer to be more specific
    alignItems: 'center', // Center the icon horizontally
    marginBottom: spacing.large, // Use large spacing below the icon
  },
  loginSubtitle: { // Renamed from subtitle
    fontSize: typography.fontSizeLarge, // Use large font size from typography
    color: COLORS.textSecondary, // Use secondary text color
    textAlign: 'center', // Center the text
    marginBottom: spacing.xLarge, // Use extra large spacing below the subtitle
  },
  loginActivityIndicator: { // Renamed from activityIndicator
    color: COLORS.loginButton, // Use button text color for indicator
  },
  // Note: errorText, linkText, input, button, buttonText are used directly from common styles
});
