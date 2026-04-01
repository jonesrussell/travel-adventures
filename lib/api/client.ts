import { z } from 'zod';

import { env } from '@/lib/env';

import { clearStoredToken, getStoredToken } from './token-storage';

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public body?: unknown,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

type Json = Record<string, unknown> | unknown[] | string | number | boolean | null;

function joinUrl(base: string, path: string): string {
  const b = base.replace(/\/$/, '');
  const p = path.startsWith('/') ? path : `/${path}`;
  return `${b}${p}`;
}

export async function apiFetch<T>(
  path: string,
  options: RequestInit & { schema?: z.ZodType<T> } = {},
): Promise<T> {
  const { schema, headers: initHeaders, ...init } = options;
  const base = env.apiBaseUrl;
  if (!base) {
    throw new ApiError(0, 'EXPO_PUBLIC_API_BASE_URL is not set');
  }

  const url = joinUrl(base, path);
  const headers = new Headers(initHeaders);
  if (!headers.has('Content-Type') && init.body !== undefined) {
    headers.set('Content-Type', 'application/json');
  }

  const token = await getStoredToken();
  if (token) {
    headers.set('Authorization', `Bearer ${token}`);
  }

  const res = await fetch(url, { ...init, headers });

  const text = await res.text();
  let data: Json = null;
  if (text) {
    try {
      data = JSON.parse(text) as Json;
    } catch {
      data = null;
    }
  }

  if (res.status === 401) {
    await clearStoredToken();
  }

  if (!res.ok) {
    const message =
      typeof data === 'object' && data !== null && 'message' in data
        ? String((data as { message: unknown }).message)
        : res.statusText;
    throw new ApiError(res.status, message || 'Request failed', data);
  }

  if (schema) {
    return schema.parse(data);
  }

  return data as T;
}
