import { Redirect, Stack } from 'expo-router';

import { useAuth } from '@/contexts/AuthContext';

export default function AppGroupLayout() {
  const { user, isReady } = useAuth();
  if (!isReady) return null;
  if (!user) return <Redirect href="/(auth)/login" />;
  return (
    <Stack>
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      <Stack.Screen name="adventure/[id]" options={{ title: 'Adventure' }} />
    </Stack>
  );
}
