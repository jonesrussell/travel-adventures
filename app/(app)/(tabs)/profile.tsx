import { useRouter } from 'expo-router';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  StyleSheet,
} from 'react-native';

import { Text, View } from '@/components/Themed';
import { useAuth } from '@/contexts/AuthContext';
import { useMyAdventures } from '@/lib/hooks/useAdventures';

export default function ProfileScreen() {
  const { user, signOut } = useAuth();
  const router = useRouter();
  const { data, isLoading, error, refetch } = useMyAdventures();

  async function onSignOut() {
    await signOut();
    router.replace('/(auth)/login');
  }

  return (
    <View style={styles.flex}>
      <View style={styles.header}>
        <Text style={styles.name}>{user?.name ?? 'Traveler'}</Text>
        <Text style={styles.email}>{user?.email}</Text>
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Sign out"
          style={styles.signOut}
          onPress={onSignOut}>
          <Text style={styles.signOutText}>Sign out</Text>
        </Pressable>
      </View>
      <Text style={styles.section}>Your adventures</Text>
      {isLoading ? (
        <ActivityIndicator style={styles.loader} />
      ) : error ? (
        <Text style={styles.error}>Could not load your posts.</Text>
      ) : (
        <FlatList
          data={data ?? []}
          keyExtractor={(item) => item.id}
          refreshing={false}
          onRefresh={() => refetch()}
          ListEmptyComponent={
            <Text style={styles.empty}>You have not posted yet.</Text>
          }
          renderItem={({ item }) => (
            <Pressable
              accessibilityRole="button"
              accessibilityLabel={`Open ${item.title}`}
              onPress={() => router.push(`/(app)/adventure/${item.id}`)}>
              <View style={styles.row}>
                <Text style={styles.rowTitle}>{item.title}</Text>
                <Text numberOfLines={2} style={styles.rowBody}>
                  {item.body}
                </Text>
              </View>
            </Pressable>
          )}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  header: { padding: 16, borderBottomWidth: StyleSheet.hairlineWidth, borderBottomColor: '#ccc' },
  name: { fontSize: 22, fontWeight: '700' },
  email: { fontSize: 15, opacity: 0.8, marginTop: 4 },
  signOut: { alignSelf: 'flex-start', marginTop: 12 },
  signOutText: { color: '#d33', fontWeight: '600' },
  section: { fontSize: 16, fontWeight: '600', padding: 16, paddingBottom: 8 },
  loader: { marginTop: 24 },
  error: { padding: 16, color: '#d33' },
  empty: { padding: 16, opacity: 0.7 },
  row: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: '#ccc',
  },
  rowTitle: { fontSize: 16, fontWeight: '600' },
  rowBody: { marginTop: 4, fontSize: 14, opacity: 0.85 },
});
