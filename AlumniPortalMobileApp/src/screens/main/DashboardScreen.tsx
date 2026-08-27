import React, {useEffect, useState} from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
  RefreshControl,
  Dimensions,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import Icon from 'react-native-vector-icons/MaterialIcons';
import {useAuth} from '../../context/AuthContext';
import {Colors} from '../../utils/Colors';
import {SCREENS} from '../../utils/Constants';

const {width} = Dimensions.get('window');

interface Props {
  navigation: any;
}

interface DashboardStats {
  totalAlumni: number;
  upcomingEvents: number;
  latestNews: number;
}

interface RecentEvent {
  id: string;
  title: string;
  date: string;
  location: string;
  description: string;
}

const DashboardScreen: React.FC<Props> = ({navigation}) => {
  const {user, logout} = useAuth();
  const [stats, setStats] = useState<DashboardStats>({
    totalAlumni: 0,
    upcomingEvents: 0,
    latestNews: 0,
  });
  const [recentEvents, setRecentEvents] = useState<RecentEvent[]>([]);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      // TODO: Replace with actual API calls
      // const response = await fetchDashboardStats();
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      setStats({
        totalAlumni: 1250,
        upcomingEvents: 8,
        latestNews: 15,
      });

      setRecentEvents([
        {
          id: '1',
          title: 'Alumni Networking Event 2024',
          date: '2024-02-15',
          location: 'University Campus',
          description: 'Join us for an evening of networking and reconnecting with fellow alumni.',
        },
        {
          id: '2',
          title: 'Career Development Workshop',
          date: '2024-02-20',
          location: 'Online Event',
          description: 'Learn about the latest trends in your industry.',
        },
        {
          id: '3',
          title: 'Annual Alumni Gala',
          date: '2024-03-10',
          location: 'Grand Hotel',
          description: 'Celebrate achievements and honor distinguished alumni.',
        },
      ]);
    } catch (error) {
      console.error('Error loading dashboard data:', error);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadDashboardData();
    setRefreshing(false);
  };

  const quickActions = [
    {
      title: 'Browse Events',
      subtitle: 'Discover upcoming events',
      icon: 'event',
      color: Colors.primary,
      onPress: () => navigation.navigate('EventsTab'),
    },
    {
      title: 'Alumni Directory',
      subtitle: 'Connect with alumni',
      icon: 'people',
      color: Colors.success,
      onPress: () => navigation.navigate('AlumniTab'),
    },
    {
      title: 'Messages',
      subtitle: 'Chat with alumni',
      icon: 'chat',
      color: Colors.info,
      onPress: () => navigation.navigate('MessagesTab'),
    },
    {
      title: 'Notifications',
      subtitle: 'View updates',
      icon: 'notifications',
      color: Colors.error,
      onPress: () => navigation.navigate(SCREENS.NOTIFICATIONS),
    },
    {
      title: 'Update Profile',
      subtitle: 'Manage your info',
      icon: 'person',
      color: Colors.warning,
      onPress: () => navigation.navigate('ProfileTab'),
    },
  ];

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  return (
    <LinearGradient
      colors={[Colors.gradientStart, Colors.gradientEnd]}
      style={styles.container}>
      <ScrollView
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl 
            refreshing={refreshing} 
            onRefresh={onRefresh}
            tintColor={Colors.white}
          />
        }>
        
        {/* Welcome Card */}
        <View style={styles.welcomeCard}>
          <View style={styles.welcomeContent}>
            <View style={styles.welcomeTextSection}>
              <Text style={styles.welcomeTitle}>
                Welcome back, {user?.firstName || 'Alumni'}!
              </Text>
              <Text style={styles.welcomeSubtitle}>
                Connect with your alumni community and stay updated with the latest events and opportunities.
              </Text>
            </View>
            
            {/* Alumni Logo */}
            <View style={styles.logoContainer}>
              <View style={styles.logoCircle}>
                <Text style={styles.logoEmoji}>🎓</Text>
              </View>
            </View>
          </View>
        </View>

        {/* Statistics Cards */}
        <View style={styles.statsContainer}>
          <View style={styles.statCard}>
            <Text style={styles.statNumber}>{stats.totalAlumni}</Text>
            <Text style={styles.statLabel}>Active Alumni</Text>
          </View>
          
          <View style={styles.statCard}>
            <Text style={[styles.statNumber, {color: Colors.success}]}>
              {stats.upcomingEvents}
            </Text>
            <Text style={styles.statLabel}>Upcoming Events</Text>
          </View>
          
          <View style={styles.statCard}>
            <Text style={[styles.statNumber, {color: Colors.warning}]}>
              {stats.latestNews}
            </Text>
            <Text style={styles.statLabel}>Latest News</Text>
          </View>
        </View>

        {/* Quick Actions */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          <View style={styles.quickActionsContainer}>
            {quickActions.map((action, index) => (
              <TouchableOpacity
                key={index}
                style={styles.quickActionCard}
                onPress={action.onPress}
                activeOpacity={0.7}>
                <View style={[styles.quickActionIcon, {backgroundColor: action.color}]}>
                  <Icon name={action.icon} size={28} color={Colors.white} />
                </View>
                <Text style={styles.quickActionTitle}>{action.title}</Text>
                <Text style={styles.quickActionSubtitle}>{action.subtitle}</Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* Upcoming Events */}
        {recentEvents.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Upcoming Events</Text>
            {recentEvents.map((event, index) => (
              <TouchableOpacity
                key={event.id}
                style={[
                  styles.eventCard,
                  {marginBottom: index === recentEvents.length - 1 ? 0 : 12}
                ]}
                onPress={() => navigation.navigate('EventsTab')}
                activeOpacity={0.7}>
                <Text style={styles.eventTitle}>{event.title}</Text>
                <View style={styles.eventMeta}>
                  <View style={styles.eventMetaItem}>
                    <Icon name="event" size={14} color={Colors.textSecondary} />
                    <Text style={styles.eventMetaText}>{formatDate(event.date)}</Text>
                  </View>
                  {event.location && (
                    <View style={styles.eventMetaItem}>
                      <Icon name="location-on" size={14} color={Colors.textSecondary} />
                      <Text style={styles.eventMetaText}>{event.location}</Text>
                    </View>
                  )}
                </View>
                <Text style={styles.eventDescription} numberOfLines={2}>
                  {event.description}
                </Text>
                <View style={styles.eventFooter}>
                  <Text style={styles.viewDetailsText}>View Details</Text>
                  <Icon name="arrow-forward" size={16} color={Colors.primary} />
                </View>
              </TouchableOpacity>
            ))}
          </View>
        )}

        {/* Logout Button */}
        <TouchableOpacity 
          style={styles.logoutButton} 
          onPress={logout}
          activeOpacity={0.7}>
          <Icon name="logout" size={20} color={Colors.error} />
          <Text style={styles.logoutText}>Logout</Text>
        </TouchableOpacity>
        
        <View style={{height: 20}} />
      </ScrollView>
    </LinearGradient>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  header: {
    paddingTop: 40,
    paddingBottom: 20,
    paddingHorizontal: 20,
  },
  headerContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  welcomeSection: {
    flex: 1,
  },
  welcomeText: {
    fontSize: 16,
    color: Colors.white,
    opacity: 0.8,
  },
  userName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: Colors.white,
  },
  profileButton: {
    padding: 4,
  },
  content: {
    flex: 1,
    paddingHorizontal: 20,
  },
  statsContainer: {
    marginTop: -30,
    marginBottom: 20,
  },
  statsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  statCard: {
    flex: 1,
    backgroundColor: Colors.white,
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
    marginHorizontal: 6,
    shadowColor: Colors.shadow,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  statNumber: {
    fontSize: 24,
    fontWeight: 'bold',
    color: Colors.white,
    marginTop: 8,
  },
  statLabel: {
    fontSize: 12,
    color: Colors.white,
    opacity: 0.9,
    marginTop: 4,
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: Colors.textPrimary,
    marginBottom: 12,
  },
  quickActionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  quickActionCard: {
    width: '48%',
    backgroundColor: Colors.white,
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: Colors.shadow,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  quickActionIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  quickActionText: {
    fontSize: 14,
    fontWeight: '500',
    color: Colors.textPrimary,
  },
  activityCard: {
    backgroundColor: Colors.white,
    borderRadius: 12,
    padding: 16,
    shadowColor: Colors.shadow,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  activityItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: Colors.borderLight,
  },
  activityText: {
    flex: 1,
    fontSize: 14,
    color: Colors.textPrimary,
    marginLeft: 12,
  },
  activityTime: {
    fontSize: 12,
    color: Colors.textSecondary,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: Colors.white,
    borderRadius: 12,
    padding: 16,
    marginBottom: 20,
    shadowColor: Colors.shadow,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  logoutText: {
    fontSize: 16,
    fontWeight: '500',
    color: Colors.error,
    marginLeft: 8,
  },
});

export default DashboardScreen;