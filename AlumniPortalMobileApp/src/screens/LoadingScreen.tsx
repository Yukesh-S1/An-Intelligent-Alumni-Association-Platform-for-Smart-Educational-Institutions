import React from 'react';
import {View, ActivityIndicator, StyleSheet, Text} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import {Colors} from '../utils/Colors';

const LoadingScreen: React.FC = () => {
  return (
    <LinearGradient
      colors={[Colors.gradientStart, Colors.gradientEnd]}
      style={styles.container}>
      <View style={styles.content}>
        {/* Alumni Logo */}
        <View style={styles.logoContainer}>
          <Text style={styles.logoText}>🎓</Text>
          <Text style={styles.appName}>Alumni Portal</Text>
        </View>
        
        <ActivityIndicator 
          size="large" 
          color={Colors.white} 
          style={styles.loader}
        />
        
        <Text style={styles.loadingText}>Loading...</Text>
      </View>
    </LinearGradient>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    alignItems: 'center',
  },
  logoContainer: {
    alignItems: 'center',
    marginBottom: 40,
  },
  logoText: {
    fontSize: 80,
    marginBottom: 16,
  },
  appName: {
    fontSize: 28,
    fontWeight: 'bold',
    color: Colors.white,
    textAlign: 'center',
  },
  loader: {
    marginBottom: 20,
  },
  loadingText: {
    fontSize: 16,
    color: Colors.white,
    opacity: 0.8,
  },
});

export default LoadingScreen;