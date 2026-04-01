import type { AdventurePost, User } from '@/types/api';

export const mockUser: User = {
  id: 'user-mock-1',
  email: 'traveler@example.com',
  name: 'Alex Traveler',
};

const now = new Date().toISOString();

export const mockAdventures: AdventurePost[] = [
  {
    id: 'adv-1',
    title: 'Sunrise in Lisbon',
    body: 'Caught the tram up to Alfama before dawn. Worth every minute of lost sleep.',
    location: 'Lisbon, Portugal',
    media_urls: [],
    created_at: now,
    user_id: mockUser.id,
  },
  {
    id: 'adv-2',
    title: 'Kyoto side streets',
    body: 'No itinerary — just walked until the lanterns came on.',
    location: 'Kyoto, Japan',
    media_urls: [],
    created_at: now,
    user_id: mockUser.id,
  },
];
