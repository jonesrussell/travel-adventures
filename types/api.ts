import { z } from 'zod';

export const userSchema = z.object({
  id: z.string(),
  email: z.string().email(),
  name: z.string().optional(),
});

export type User = z.infer<typeof userSchema>;

export const adventurePostSchema = z.object({
  id: z.string(),
  title: z.string(),
  body: z.string(),
  location: z.string().nullable().optional(),
  media_urls: z.array(z.string()).optional(),
  created_at: z.string(),
  user_id: z.string(),
});

export type AdventurePost = z.infer<typeof adventurePostSchema>;

export const authResponseSchema = z.object({
  token: z.string(),
  user: userSchema,
});

export type AuthResponse = z.infer<typeof authResponseSchema>;
