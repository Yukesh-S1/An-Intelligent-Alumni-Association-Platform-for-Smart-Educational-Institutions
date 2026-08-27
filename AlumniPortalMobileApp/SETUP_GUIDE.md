# Alumni Portal Mobile App - Complete Setup Guide

## 📋 Prerequisites Checklist

Before starting, ensure you have:

- [ ] **Node.js 16+** installed
- [ ] **Android Studio** with Android SDK
- [ ] **Java Development Kit (JDK) 11+**
- [ ] **React Native CLI** installed globally
- [ ] **Git** for version control
- [ ] **Physical Android device** or **Android Emulator**

## 🔧 Step-by-Step Setup

### Step 1: Install Development Environment

#### 1.1 Install Node.js
```bash
# Download and install from: https://nodejs.org/
# Verify installation
node --version
npm --version
```

#### 1.2 Install React Native CLI
```bash
npm install -g react-native-cli
# Verify installation
react-native --version
```

#### 1.3 Install Android Studio
1. Download from: https://developer.android.com/studio
2. Install with default settings
3. Open Android Studio and complete initial setup

#### 1.4 Configure Android SDK
1. Open Android Studio
2. Go to **Tools → SDK Manager**
3. Install the following:
   - **Android SDK Platform 33**
   - **Android SDK Build-Tools 33.0.0**
   - **Android Emulator**
   - **Android SDK Platform-Tools**

### Step 2: Set Environment Variables

#### For Windows:
1. Open **System Properties → Advanced → Environment Variables**
2. Add new system variables:
   - `ANDROID_HOME`: `C:\Users\YourUsername\AppData\Local\Android\Sdk`
   - Add to `PATH`: `%ANDROID_HOME%\platform-tools`
   - Add to `PATH`: `%ANDROID_HOME%\tools`
   - Add to `PATH`: `%ANDROID_HOME%\tools\bin`

#### For macOS/Linux:
```bash
# Add to ~/.bashrc or ~/.zshrc
export ANDROID_HOME=$HOME/Android/Sdk
export PATH=$PATH:$ANDROID_HOME/emulator
export PATH=$PATH:$ANDROID_HOME/tools
export PATH=$PATH:$ANDROID_HOME/tools/bin
export PATH=$PATH:$ANDROID_HOME/platform-tools

# Reload shell configuration
source ~/.bashrc  # or source ~/.zshrc
```

### Step 3: Project Setup

#### 3.1 Navigate to Project Directory
```bash
# Move the AlumniPortalMobileApp folder to your desired location
# For example: C:\Projects\ or ~/Projects/
cd /path/to/AlumniPortalMobileApp
```

#### 3.2 Install Dependencies
```bash
# Install npm packages
npm install

# Clear npm cache if needed
npm cache clean --force
```

#### 3.3 Configure API Connection
Edit `src/utils/Constants.ts`:
```typescript
export const API_CONFIG = {
  BASE_URL: 'http://192.168.1.100', // Replace with your server IP
  // For local development, use your computer's IP address
  // NOT localhost or 127.0.0.1 (won't work on physical device)
  ENDPOINTS: {
    // ... existing endpoints
  },
};
```

**Important:** Use your computer's actual IP address, not localhost!

### Step 4: Android Device Setup

#### Option A: Physical Device
1. **Enable Developer Options:**
   - Go to Settings → About Phone
   - Tap "Build Number" 7 times
   - Go back to Settings → Developer Options

2. **Enable USB Debugging:**
   - In Developer Options, enable "USB Debugging"
   - Connect device via USB
   - Allow USB debugging when prompted

3. **Verify Device Connection:**
   ```bash
   adb devices
   # Should show your device listed
   ```

#### Option B: Android Emulator
1. **Create Virtual Device:**
   - Open Android Studio
   - Go to Tools → AVD Manager
   - Click "Create Virtual Device"
   - Choose a device (e.g., Pixel 4)
   - Select Android 9+ system image
   - Finish setup

2. **Start Emulator:**
   - Click play button in AVD Manager
   - Wait for emulator to fully boot

### Step 5: Run the Application

#### 5.1 Start Metro Bundler
```bash
# In project directory
npm start
# or
npx react-native start
```

#### 5.2 Run on Android (New Terminal)
```bash
# Make sure device/emulator is connected
npm run android
# or
npx react-native run-android
```

### Step 6: Verify Installation

If everything is set up correctly, you should see:
1. Metro bundler running in terminal
2. App installing on device/emulator
3. Alumni Portal splash screen
4. Login screen appears

## 🐛 Troubleshooting Common Issues

### Issue 1: "SDK location not found"
**Solution:**
```bash
# Create local.properties file in android/ directory
echo "sdk.dir=C:\\Users\\YourUsername\\AppData\\Local\\Android\\Sdk" > android/local.properties
# (Adjust path for your system)
```

### Issue 2: "Unable to load script from assets"
**Solution:**
```bash
# Clear React Native cache
npx react-native start --reset-cache
```

### Issue 3: "INSTALL_FAILED_INSUFFICIENT_STORAGE"
**Solution:**
- Free up space on device/emulator
- Or use a different emulator with more storage

### Issue 4: "Command failed: gradlew.bat assembleDebug"
**Solution:**
```bash
# Clean and rebuild
cd android
./gradlew clean
cd ..
npm run android
```

### Issue 5: "Network request failed"
**Solution:**
- Check if your PHP server is running
- Verify the IP address in Constants.ts
- Ensure device and server are on same network
- Check firewall settings

### Issue 6: Metro bundler port conflict
**Solution:**
```bash
# Kill existing Metro processes
npx react-native start --port=8082
```

## 🔧 Development Tips

### Hot Reloading
- Shake device or press `Ctrl+M` (emulator) to open developer menu
- Enable "Fast Refresh" for automatic reloading

### Debugging
- Use React Native Debugger
- Enable "Debug JS Remotely" from developer menu
- Use `console.log()` statements for debugging

### Building APK
```bash
# Debug APK
cd android
./gradlew assembleDebug

# Release APK (for distribution)
./gradlew assembleRelease
```

## 📱 Testing Checklist

Before considering setup complete, test:
- [ ] App launches successfully
- [ ] Login screen appears
- [ ] Navigation works
- [ ] API calls work (if server is running)
- [ ] Hot reloading works
- [ ] App doesn't crash on basic interactions

## 🚀 Next Steps

After successful setup:
1. **Configure your PHP backend** to accept mobile API requests
2. **Update API endpoints** in the app
3. **Test all features** with real data
4. **Customize branding** (colors, icons, app name)
5. **Add additional features** as needed

## 📞 Getting Help

If you encounter issues:
1. Check this troubleshooting guide
2. Review React Native documentation
3. Check Android Studio logs
4. Use `adb logcat` for detailed Android logs
5. Search for specific error messages online

## 🎯 Success Indicators

Setup is successful when:
- ✅ App builds without errors
- ✅ App runs on device/emulator
- ✅ No red error screens
- ✅ Navigation between screens works
- ✅ Developer menu is accessible

---

**Congratulations!** 🎉 Your Alumni Portal Mobile App is now set up and ready for development!