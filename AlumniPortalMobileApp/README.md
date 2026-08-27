# Alumni Portal Mobile App

A React Native mobile application for the Alumni Portal system, supporting Android 9+ devices.

## 🚀 Features

- **Authentication** - Login/Register with existing portal
- **Dashboard** - Alumni statistics and quick actions
- **Events** - Browse and register for events
- **Alumni Directory** - Connect with fellow alumni
- **Messages** - Real-time chat system
- **Video Calls** - Video calling functionality
- **Notifications** - Push notifications for updates
- **Profile Management** - Update profile and upload photos
- **Feedback System** - Submit feedback and suggestions
- **News & Updates** - Latest alumni news

## 📱 Requirements

- **Android:** Version 9+ (API Level 28+)
- **Node.js:** Version 16 or higher
- **React Native CLI:** Latest version
- **Android Studio:** Latest version with Android SDK

## 🛠️ Setup Instructions

### 1. Prerequisites

Install the required tools:

```bash
# Install Node.js (if not already installed)
# Download from: https://nodejs.org/

# Install React Native CLI
npm install -g react-native-cli

# Install Android Studio
# Download from: https://developer.android.com/studio
```

### 2. Clone and Setup

```bash
# Navigate to your desired directory
cd /path/to/your/projects

# Copy the AlumniPortalMobileApp folder to your desired location
# Then navigate to the project directory
cd AlumniPortalMobileApp

# Install dependencies
npm install

# For iOS (if planning to support iOS later)
cd ios && pod install && cd ..
```

### 3. Android Setup

1. **Open Android Studio**
2. **Configure Android SDK:**
   - Go to Tools → SDK Manager
   - Install Android SDK Platform 33
   - Install Android SDK Build-Tools 33.0.0
   - Install Android Emulator

3. **Set Environment Variables:**
   ```bash
   # Add to your ~/.bashrc or ~/.zshrc
   export ANDROID_HOME=$HOME/Android/Sdk
   export PATH=$PATH:$ANDROID_HOME/emulator
   export PATH=$PATH:$ANDROID_HOME/tools
   export PATH=$PATH:$ANDROID_HOME/tools/bin
   export PATH=$PATH:$ANDROID_HOME/platform-tools
   ```

### 4. Configuration

1. **Update API Configuration:**
   Edit `src/utils/Constants.ts` and update the `BASE_URL`:
   ```typescript
   export const API_CONFIG = {
     BASE_URL: 'http://your-server.com', // Replace with your PHP server URL
     // ... rest of config
   };
   ```

2. **Configure App Details:**
   Update app name and package in:
   - `android/app/src/main/res/values/strings.xml`
   - `android/app/build.gradle` (applicationId)

### 5. Running the App

#### Development Mode

```bash
# Start Metro bundler
npm start

# Run on Android (in a new terminal)
npm run android

# Or run with specific device
npx react-native run-android --deviceId=DEVICE_ID
```

#### Build for Production

```bash
# Generate release APK
npm run build-android

# The APK will be generated at:
# android/app/build/outputs/apk/release/app-release.apk
```

## 🔧 Development

### Project Structure

```
src/
├── components/         # Reusable UI components
├── screens/           # App screens
│   ├── auth/         # Authentication screens
│   ├── main/         # Main app screens
│   └── details/      # Detail screens
├── navigation/        # Navigation setup
├── context/          # React Context providers
├── services/         # API services
├── utils/            # Utility functions and constants
└── assets/           # Images, fonts, etc.
```

### Key Files

- **`src/App.tsx`** - Main app component
- **`src/utils/Constants.ts`** - App configuration
- **`src/utils/Colors.ts`** - Color palette
- **`src/context/AuthContext.tsx`** - Authentication management
- **`src/navigation/`** - Navigation structure

### Adding New Screens

1. Create screen component in appropriate folder
2. Add to navigation in `src/navigation/MainNavigator.tsx`
3. Add screen name to `src/utils/Constants.ts`

### API Integration

The app is designed to work with your existing PHP backend. Update the API endpoints in `src/utils/Constants.ts` and implement the actual API calls in the respective screens.

## 📦 Building APK

### Debug APK
```bash
cd android
./gradlew assembleDebug
```

### Release APK
```bash
cd android
./gradlew assembleRelease
```

The APK files will be generated in:
- Debug: `android/app/build/outputs/apk/debug/`
- Release: `android/app/build/outputs/apk/release/`

## 🎨 Customization

### Colors
Update the color scheme in `src/utils/Colors.ts` to match your branding.

### Icons
Replace icons in `android/app/src/main/res/mipmap-*/` directories.

### Splash Screen
Update splash screen in `android/app/src/main/res/drawable/`.

## 🔍 Troubleshooting

### Common Issues

1. **Metro bundler issues:**
   ```bash
   npm start -- --reset-cache
   ```

2. **Android build issues:**
   ```bash
   cd android
   ./gradlew clean
   cd ..
   npm run android
   ```

3. **Permission issues:**
   - Ensure all required permissions are in AndroidManifest.xml
   - Request runtime permissions for camera, storage, etc.

### Debugging

- Use React Native Debugger
- Check Metro bundler logs
- Use `adb logcat` for Android logs

## 📱 Testing

### On Physical Device

1. Enable Developer Options on your Android device
2. Enable USB Debugging
3. Connect device via USB
4. Run `npm run android`

### On Emulator

1. Start Android emulator from Android Studio
2. Run `npm run android`

## 🚀 Deployment

### Google Play Store

1. Generate signed APK
2. Create Google Play Console account
3. Upload APK and fill store listing
4. Submit for review

### Internal Distribution

- Share the APK file directly
- Use Firebase App Distribution
- Use internal testing tracks

## 📄 License

This project is licensed under the MIT License.

## 🤝 Support

For support and questions:
- Check the troubleshooting section
- Review React Native documentation
- Contact the development team

---

**Note:** This mobile app is designed to work with your existing Alumni Portal PHP backend. Make sure to update the API endpoints and implement proper authentication before deploying to production.