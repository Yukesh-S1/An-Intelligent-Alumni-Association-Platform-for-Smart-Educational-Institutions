import React, {useEffect, useState} from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  RefreshControl,
  Image,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import {Colors} from '../../utils/Colors';
import {SCREENS} from '../../utils/Constants';

interface Props {
  navigation: any;
}

interface Event {
  id: string;
  title: string;
  description: string;
  date: string;
  location: string;
  image?: string;
  participantCount: number;
}

const EventsScreen: React.FC<Props> = ({navigation}) => {
  const [events, setEvents] = useState<Event[]>([]);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadEvents();
  }, []);

  const loadEvents = async () => {
    try {
      // TODO: Replace with actual API call
      // const response = await fetchEvents();
      
      // Simulate API call with mock data
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      const mockEvents: Event[] = [
        {
          id: '1',
          title: 'Alumni Networking Event 2024',
          description: 'Join us for an evening of networking and reconnecting with fellow alumni.',
          date: '2024-02-15',
          location: 'University Campus',
          participantCount: 45,
        },
        {
          id: '2',
          title: 'Career Development Workshop',
          description: 'Learn about the latest trends in your industry and advance your career.',
          date: '2024-02-20',
          location: 'Online Event',
          participantCount: 78,
        },
        {
          id: '3',
          title: 'Annual Alumni Gala',
          description: 'Celebrate achievements and honor distinguished alumni at our annual gala.',
          date: '2024-03-10',
          location: 'Grand Hotel Ballroom',
          participantCount: 120,
        },
      ];
      
      setEvents(mockEvents);
    } catch (error) {
      console.error('Error loading events:', error);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadEvents();
    setRefreshing(false);
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  const renderEventItem = ({item}: {item: Event}) => (
    <TouchableOpacity
      style={styles.eventCard}
      onPress={() => navigation.navigate(SCREENS.EVENT_DETAILS, {event: item})}>
      <View style={styles.eventHeader}>
        <View style={styles.dateContainer}>
          <Text style={styles.dateText}>{formatDate(item.date)}</Text>
        </View>
        <View style={styles.participantCount}>
          <Icon name="people" size={16} color={Colors.textSecondary} />
          <Text style={styles.participantText}>{item.participantCount}</Text>
        </View>
      </View>
      
      <Text style={styles.eventTitle}>{item.title}</Text>
      <Text style={styles.eventDescription} numberOfLines={2}>
        {item.description}
      </Text>
      
      <View style={styles.eventFooter}>
        <View style={styles.locationContainer}>
          <Icon name="location-on" size={16} color={Colors.textSecondary} />
          <Text style={styles.locationText}>{item.location}</Text>
        </View>
        <TouchableOpacity style={styles.registerButton}>
          <Text style={styles.registerButtonText}>Register</Text>
        </TouchableOpacity>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Events</Text>
        <TouchableOpacity style={styles.filterButton}>
          <Icon name="filter-list" size={24} color={Colors.primary} />
        </TouchableOpacity>
      </View>

      {/* Events List */}
      <FlatList
        data={events}
        renderItem={renderEventItem}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.listContainer}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        showsVerticalScrollIndicator={false}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 40,
    paddingBottom: 20,
    backgroundColor: Colors.white,
    shadowColor: Colors.shadow,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: Colors.textPrimary,
  },
  filterButton: {
    padding: 8,
  },
  listContainer: {
    padding: 20,
  },
  eventCard: {
    backgroundColor: Colors.white,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    shadowColor: Colors.shadow,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 4,
  },
  eventHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  dateContainer: {
    backgroundColor: Colors.primary,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
  },
  dateText: {
    fontSize: 12,
    fontWeight: '500',
    color: Colors.white,
  },
  participantCount: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  participantText: {
    fontSize: 12,
    color: Colors.textSecondary,
    marginLeft: 4,
  },
  eventTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: Colors.textPrimary,
    marginBottom: 8,
  },
  eventDescription: {
    fontSize: 14,
    color: Colors.textSecondary,
    lineHeight: 20,
    marginBottom: 12,
  },
  eventFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  locationText: {
    fontSize: 12,
    color: Colors.textSecondary,
    marginLeft: 4,
  },
  registerButton: {
    backgroundColor: Colors.primary,
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 16,
  },
  registerButtonText: {
    fontSize: 12,
    fontWeight: '500',
    color: Colors.white,
  },
});

export default EventsScreen;