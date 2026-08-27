/**
 * App constants and configuration
 */

// API Configuration
export const API_CONFIG = {
  // Replace with your actual server URL
  BASE_URL: 'http://your-server.com', // Change this to your PHP server URL
  ENDPOINTS: {
    LOGIN: '/login.php',
    REGISTER: '/registration.php',
    DASHBOARD: '/api/dashboard.php',
    EVENTS: '/api/events.php',
    ALUMNI: '/api/alumni.php',
    MESSAGES: '/api/messages.php',
    NOTIFICATIONS: '/api/notifications.php',
    PROFILE: '/api/profile.php',
    FEEDBACK: '/api/feedback.php',
    VIDEO_CALLS: '/api/video_calls.php',
  },
  TIMEOUT: 10000, // 10 seconds
};

// App Configuration
export const APP_CONFIG = {
  NAME: 'Alumni Portal',
  VERSION: '1.0.0',
  MIN_ANDROID_VERSION: 28, // Android 9
  SUPPORTED_ORIENTATIONS: ['portrait'],
};

// Screen Names
export const SCREENS = {
  // Auth Screens
  LOGIN: 'Login',
  REGISTER: 'Register',
  FORGOT_PASSWORD: 'ForgotPassword',
  
  // Main Screens
  DASHBOARD: 'Dashboard',
  EVENTS: 'Events',
  EVENT_DETAILS: 'EventDetails',
  ALUMNI: 'Alumni',
  ALUMNI_PROFILE: 'AlumniProfile',
  MESSAGES: 'Messages',
  CHAT: 'Chat',
  VIDEO_CALL: 'VideoCall',
  NOTIFICATIONS: 'Notifications',
  PROFILE: 'Profile',
  EDIT_PROFILE: 'EditProfile',
  FEEDBACK: 'Feedback',
  NEWS: 'News',
  SETTINGS: 'Settings',
};

// Storage Keys
export const STORAGE_KEYS = {
  USER_TOKEN: 'user_token',
  USER_DATA: 'user_data',
  REMEMBER_ME: 'remember_me',
  PUSH_TOKEN: 'push_token',
};

// Dimensions
export const DIMENSIONS = {
  HEADER_HEIGHT: 60,
  TAB_BAR_HEIGHT: 60,
  BUTTON_HEIGHT: 48,
  INPUT_HEIGHT: 48,
  CARD_BORDER_RADIUS: 12,
  BUTTON_BORDER_RADIUS: 8,
};

// Animation Durations
export const ANIMATIONS = {
  FAST: 200,
  NORMAL: 300,
  SLOW: 500,
};