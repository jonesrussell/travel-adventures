import { z } from 'zod';

import {
  adventurePostSchema,
  type AdventurePost,
} from '@/types/api';

import { env } from '@/lib/env';

import { mockAdventures } from './mock-data';
import { apiFetch } from './client';

const listSchema = z.array(adventurePostSchema);

export async function fetchAdventures(): Promise<AdventurePost[]> {
  if (env.useMockApi) {
    return [...mockAdventures];
  }

  return apiFetch<AdventurePost[]>('/adventures', {
    method: 'GET',
    schema: listSchema,
  });
}

export async function fetchAdventure(id: string): Promise<AdventurePost> {
  if (env.useMockApi) {
    const found = mockAdventures.find((a) => a.id === id);
    if (!found) {
      throw new Error('Adventure not found');
    }
    return found;
  }

  return apiFetch<AdventurePost>(`/adventures/${id}`, {
    method: 'GET',
    schema: adventurePostSchema,
  });
}

export async function fetchAdventuresForUser(userId: string): Promise<AdventurePost[]> {
  if (env.useMockApi) {
    return mockAdventures.filter((a) => a.user_id === userId);
  }

  const q = new URLSearchParams({ user_id: userId });
  return apiFetch<AdventurePost[]>(`/adventures?${q.toString()}`, {
    method: 'GET',
    schema: listSchema,
  });
}

export async function createAdventure(
  input: {
    title: string;
    body: string;
    location?: string | null;
  },
  currentUserId: string,
): Promise<AdventurePost> {
  if (env.useMockApi) {
    const post: AdventurePost = {
      id: `adv-${Date.now()}`,
      title: input.title,
      body: input.body,
      location: input.location ?? null,
      media_urls: [],
      created_at: new Date().toISOString(),
      user_id: currentUserId,
    };
    mockAdventures.unshift(post);
    return post;
  }

  return apiFetch<AdventurePost>('/adventures', {
    method: 'POST',
    body: JSON.stringify(input),
    schema: adventurePostSchema,
  });
}
