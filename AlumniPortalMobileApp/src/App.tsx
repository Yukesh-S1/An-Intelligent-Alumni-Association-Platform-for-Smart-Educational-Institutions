import React, {useEffect} from 'react';
import {StatusBar, Platform} from 'react-native';
import {NavigationContainer} from '@react-navigation/native';
import {GestureHandlerRootView} from 'react-native-gesture-handler';
import SplashScreen from 'react-native-splash-screen';
import Toast from 'react-native-toast-message';

import {AuthProvider} from './context/AuthContext';
import AppNavigator from './navigation/AppNavigator';
import {Colors} from './utils/Colors';

const App: React.FC = () => {
  useEffect(() => {
    // Hide splash screen after app loads
    if (Platform.OS === 'android') {
      SplashScreen.hide();
    }
  }, []);

  return (
    <GestureHandlerRootView style={{flex: 1}}>
      <AuthProvider>
        <NavigationContainer>
          <StatusBar
            barStyle="light-content"
            backgroundColor={Colors.primary}
            translucent={false}
          />
          <AppNavigator />
          <Toast />
        </NavigationContainer>
      </AuthProvider>
    </GestureHandlerRootView>
  );
};

export default App;