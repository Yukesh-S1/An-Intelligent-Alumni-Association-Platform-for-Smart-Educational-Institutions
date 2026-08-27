/**
 * Alumni Portal Mobile App
 * Main entry point for React Native application
 */

import {AppRegistry} from 'react-native';
import App from './src/App';
import {name as appName} from './package.json';

AppRegistry.registerComponent(appName, () => App);