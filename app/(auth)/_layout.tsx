import { Redirect, Stack } from 'expo-router';

import { useAuth } from '@/contexts/AuthContext';

export default function AuthLayout() {
  const { user, isReady } = useAuth();
  if (!isReady) return null;
  if (user) return <Redirect href="/(app)/(tabs)" />;
  return <Stack />;
}
