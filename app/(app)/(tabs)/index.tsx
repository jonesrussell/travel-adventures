import { Link } from 'expo-router';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  RefreshControl,
  StyleSheet,
} from 'react-native';

import { Text, View } from '@/components/Themed';
import { useAdventuresList } from '@/lib/hooks/useAdventures';

export default function FeedScreen() {
  const { data, isLoading, isRefetching, refetch, error } = useAdventuresList();

  if (isLoading && !data) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  if (error) {
    return (
      <View style={styles.centered}>
        <Text style={styles.error}>Could not load adventures.</Text>
        <Pressable accessibilityRole="button" onPress={() => refetch()}>
          <Text style={styles.retry}>Retry</Text>
        </Pressable>
      </View>
    );
  }

  const list = data ?? [];

  return (
    <View style={styles.flex}>
      <FlatList
        data={list}
        keyExtractor={(item) => item.id}
        refreshControl={
          <RefreshControl refreshing={isRefetching} onRefresh={() => refetch()} />
        }
        ListEmptyComponent={
          <View style={styles.centered}>
            <Text style={styles.empty}>No adventures yet. Share your first trip from Create.</Text>
          </View>
        }
        renderItem={({ item }) => (
          <Link href={`/(app)/adventure/${item.id}`} asChild>
            <Pressable
              accessibilityRole="button"
              accessibilityLabel={`Open adventure ${item.title}`}
              style={styles.card}>
              <Text style={styles.cardTitle}>{item.title}</Text>
              {item.location ? (
                <Text style={styles.cardMeta}>{item.location}</Text>
              ) : null}
              <Text numberOfLines={3} style={styles.cardBody}>
                {item.body}
              </Text>
            </Pressable>
          </Link>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  error: { marginBottom: 12, textAlign: 'center' },
  retry: { color: '#2f95dc', fontWeight: '600' },
  empty: { textAlign: 'center', opacity: 0.8 },
  card: {
    padding: 16,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: '#ccc',
  },
  cardTitle: { fontSize: 18, fontWeight: '700', marginBottom: 4 },
  cardMeta: { fontSize: 14, opacity: 0.7, marginBottom: 8 },
  cardBody: { fontSize: 15, lineHeight: 22 },
});
