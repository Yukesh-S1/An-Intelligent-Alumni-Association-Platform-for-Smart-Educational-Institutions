import React from 'react';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {createStackNavigator} from '@react-navigation/stack';
import Icon from 'react-native-vector-icons/MaterialIcons';
import {Colors} from '../utils/Colors';
import {SCREENS} from '../utils/Constants';

// Screens
import DashboardScreen from '../screens/main/DashboardScreen';
import EventsScreen from '../screens/main/EventsScreen';
import AlumniScreen from '../screens/main/AlumniScreen';
import MessagesScreen from '../screens/main/MessagesScreen';
import ProfileScreen from '../screens/main/ProfileScreen';

// Detail Screens
import EventDetailsScreen from '../screens/details/EventDetailsScreen';
import AlumniProfileScreen from '../screens/details/AlumniProfileScreen';
import ChatScreen from '../screens/details/ChatScreen';
import NotificationsScreen from '../screens/main/NotificationsScreen';
import EditProfileScreen from '../screens/details/EditProfileScreen';
import FeedbackScreen from '../screens/main/FeedbackScreen';
import NewsScreen from '../screens/main/NewsScreen';

const Tab = createBottomTabNavigator();
const Stack = createStackNavigator();

// Stack Navigators for each tab
const DashboardStack = () => (
  <Stack.Navigator screenOptions={{headerShown: false}}>
    <Stack.Screen name={SCREENS.DASHBOARD} component={DashboardScreen} />
    <Stack.Screen name={SCREENS.NOTIFICATIONS} component={NotificationsScreen} />
    <Stack.Screen name={SCREENS.FEEDBACK} component={FeedbackScreen} />
    <Stack.Screen name={SCREENS.NEWS} component={NewsScreen} />
  </Stack.Navigator>
);

const EventsStack = () => (
  <Stack.Navigator screenOptions={{headerShown: false}}>
    <Stack.Screen name={SCREENS.EVENTS} component={EventsScreen} />
    <Stack.Screen name={SCREENS.EVENT_DETAILS} component={EventDetailsScreen} />
  </Stack.Navigator>
);

const AlumniStack = () => (
  <Stack.Navigator screenOptions={{headerShown: false}}>
    <Stack.Screen name={SCREENS.ALUMNI} component={AlumniScreen} />
    <Stack.Screen name={SCREENS.ALUMNI_PROFILE} component={AlumniProfileScreen} />
  </Stack.Navigator>
);

const MessagesStack = () => (
  <Stack.Navigator screenOptions={{headerShown: false}}>
    <Stack.Screen name={SCREENS.MESSAGES} component={MessagesScreen} />
    <Stack.Screen name={SCREENS.CHAT} component={ChatScreen} />
  </Stack.Navigator>
);

const ProfileStack = () => (
  <Stack.Navigator screenOptions={{headerShown: false}}>
    <Stack.Screen name={SCREENS.PROFILE} component={ProfileScreen} />
    <Stack.Screen name={SCREENS.EDIT_PROFILE} component={EditProfileScreen} />
  </Stack.Navigator>
);

const MainNavigator: React.FC = () => {
  return (
    <Tab.Navigator
      screenOptions={({route}) => ({
        tabBarIcon: ({focused, color, size}) => {
          let iconName: string;

          switch (route.name) {
            case 'DashboardTab':
              iconName = 'dashboard';
              break;
            case 'EventsTab':
              iconName = 'event';
              break;
            case 'AlumniTab':
              iconName = 'people';
              break;
            case 'MessagesTab':
              iconName = 'chat';
              break;
            case 'ProfileTab':
              iconName = 'person';
              break;
            default:
              iconName = 'help';
          }

          return <Icon name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: Colors.primary,
        tabBarInactiveTintColor: Colors.gray,
        tabBarStyle: {
          backgroundColor: Colors.white,
          borderTopColor: Colors.border,
          height: 60,
          paddingBottom: 8,
          paddingTop: 8,
        },
        tabBarLabelStyle: {
          fontSize: 12,
          fontWeight: '500',
        },
        headerShown: false,
      })}>
      <Tab.Screen
        name="DashboardTab"
        component={DashboardStack}
        options={{tabBarLabel: 'Dashboard'}}
      />
      <Tab.Screen
        name="EventsTab"
        component={EventsStack}
        options={{tabBarLabel: 'Events'}}
      />
      <Tab.Screen
        name="AlumniTab"
        component={AlumniStack}
        options={{tabBarLabel: 'Alumni'}}
      />
      <Tab.Screen
        name="MessagesTab"
        component={MessagesStack}
        options={{tabBarLabel: 'Messages'}}
      />
      <Tab.Screen
        name="ProfileTab"
        component={ProfileStack}
        options={{tabBarLabel: 'Profile'}}
      />
    </Tab.Navigator>
  );
};

export default MainNavigator;