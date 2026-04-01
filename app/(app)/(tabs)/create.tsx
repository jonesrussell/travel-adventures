import { useRouter } from 'expo-router';
import { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  StyleSheet,
  TextInput,
} from 'react-native';

import { Text, View } from '@/components/Themed';
import { ApiError } from '@/lib/api/client';
import { useCreateAdventure } from '@/lib/hooks/useAdventures';

export default function CreateScreen() {
  const router = useRouter();
  const create = useCreateAdventure();
  const [title, setTitle] = useState('');
  const [body, setBody] = useState('');
  const [location, setLocation] = useState('');

  async function onSubmit() {
    if (!title.trim() || !body.trim()) {
      Alert.alert('Missing fields', 'Add a title and your story.');
      return;
    }
    try {
      await create.mutateAsync({
        title: title.trim(),
        body: body.trim(),
        location: location.trim() || null,
      });
      setTitle('');
      setBody('');
      setLocation('');
      router.push('/(app)/(tabs)');
      Alert.alert('Posted', 'Your adventure is live.');
    } catch (e) {
      const msg = e instanceof ApiError ? e.message : 'Could not create adventure';
      Alert.alert('Error', msg);
    }
  }

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      style={styles.flex}>
      <View style={styles.container}>
        <Text style={styles.label}>Title</Text>
        <TextInput
          accessibilityLabel="Adventure title"
          placeholder="Where did you go?"
          placeholderTextColor="#888"
          style={styles.input}
          value={title}
          onChangeText={setTitle}
        />
        <Text style={styles.label}>Story</Text>
        <TextInput
          accessibilityLabel="Adventure story"
          placeholder="What happened?"
          placeholderTextColor="#888"
          multiline
          numberOfLines={8}
          style={[styles.input, styles.body]}
          textAlignVertical="top"
          value={body}
          onChangeText={setBody}
        />
        <Text style={styles.label}>Location (optional)</Text>
        <TextInput
          accessibilityLabel="Location"
          placeholder="City, country"
          placeholderTextColor="#888"
          style={styles.input}
          value={location}
          onChangeText={setLocation}
        />
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Publish adventure"
          disabled={create.isPending}
          style={[styles.button, create.isPending && styles.buttonDisabled]}
          onPress={onSubmit}>
          {create.isPending ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.buttonText}>Publish</Text>
          )}
        </Pressable>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  container: { flex: 1, padding: 16 },
  label: { fontSize: 14, fontWeight: '600', marginBottom: 6 },
  input: {
    borderWidth: StyleSheet.hairlineWidth,
    borderColor: '#ccc',
    borderRadius: 8,
    padding: 12,
    marginBottom: 16,
    fontSize: 16,
  },
  body: { minHeight: 160 },
  button: {
    backgroundColor: '#2f95dc',
    padding: 14,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 8,
  },
  buttonDisabled: { opacity: 0.6 },
  buttonText: { color: '#fff', fontSize: 16, fontWeight: '600' },
});
