import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

import { useAuth } from '@/contexts/AuthContext';
import {
  createAdventure,
  fetchAdventure,
  fetchAdventures,
  fetchAdventuresForUser,
} from '@/lib/api/adventures-service';

export function useAdventuresList() {
  return useQuery({
    queryKey: ['adventures'],
    queryFn: fetchAdventures,
  });
}

export function useAdventure(id: string | undefined) {
  return useQuery({
    queryKey: ['adventures', id],
    queryFn: () => fetchAdventure(id!),
    enabled: Boolean(id),
  });
}

export function useMyAdventures() {
  const { user } = useAuth();
  const userId = user?.id;

  return useQuery({
    queryKey: ['adventures', 'user', userId],
    queryFn: () => fetchAdventuresForUser(userId!),
    enabled: Boolean(userId),
  });
}

export function useCreateAdventure() {
  const qc = useQueryClient();
  const { user } = useAuth();

  return useMutation({
    mutationFn: (input: {
      title: string;
      body: string;
      location?: string | null;
    }) => {
      if (!user?.id) throw new Error('Not signed in');
      return createAdventure(input, user.id);
    },
    onSuccess: () => {
      void qc.invalidateQueries({ queryKey: ['adventures'] });
    },
  });
}
