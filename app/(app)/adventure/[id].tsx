import { useLocalSearchParams, useRouter } from 'expo-router';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet } from 'react-native';

import { Text, View } from '@/components/Themed';
import { useAdventure } from '@/lib/hooks/useAdventures';

export default function AdventureDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const router = useRouter();
  const { data, isLoading, error } = useAdventure(id);

  if (isLoading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  if (error || !data) {
    return (
      <View style={styles.centered}>
        <Text>Adventure not found.</Text>
        <Pressable accessibilityRole="button" onPress={() => router.back()}>
          <Text style={styles.link}>Go back</Text>
        </Pressable>
      </View>
    );
  }

  return (
    <ScrollView contentContainerStyle={styles.content}>
      <Text style={styles.title}>{data.title}</Text>
      {data.location ? <Text style={styles.meta}>{data.location}</Text> : null}
      <Text style={styles.date}>
        {new Date(data.created_at).toLocaleString()}
      </Text>
      <Text style={styles.body}>{data.body}</Text>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  link: { marginTop: 12, color: '#2f95dc', fontWeight: '600' },
  content: { padding: 20, paddingBottom: 40 },
  title: { fontSize: 26, fontWeight: '700', marginBottom: 8 },
  meta: { fontSize: 16, opacity: 0.8, marginBottom: 8 },
  date: { fontSize: 13, opacity: 0.6, marginBottom: 20 },
  body: { fontSize: 17, lineHeight: 26 },
});
