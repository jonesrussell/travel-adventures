import {
  authResponseSchema,
  type AuthResponse,
  type User,
  userSchema,
} from '@/types/api';

import { env } from '@/lib/env';

import { mockUser } from './mock-data';
import { apiFetch, ApiError } from './client';
import { clearStoredToken, setStoredToken } from './token-storage';

export async function login(email: string, password: string): Promise<AuthResponse> {
  if (env.useMockApi) {
    await setStoredToken('mock-token');
    return { token: 'mock-token', user: { ...mockUser, email } };
  }

  const res = await apiFetch<AuthResponse>('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
    schema: authResponseSchema,
  });
  await setStoredToken(res.token);
  return res;
}

export async function register(
  email: string,
  password: string,
  name?: string,
): Promise<AuthResponse> {
  if (env.useMockApi) {
    await setStoredToken('mock-token');
    return {
      token: 'mock-token',
      user: { ...mockUser, email, name: name ?? mockUser.name },
    };
  }

  const res = await apiFetch<AuthResponse>('/auth/register', {
    method: 'POST',
    body: JSON.stringify({ email, password, name }),
    schema: authResponseSchema,
  });
  await setStoredToken(res.token);
  return res;
}

export async function fetchCurrentUser(): Promise<User> {
  if (env.useMockApi) {
    return mockUser;
  }

  return apiFetch<User>('/users/me', {
    method: 'GET',
    schema: userSchema,
  });
}

export async function logout(): Promise<void> {
  await clearStoredToken();
}

export { ApiError };
