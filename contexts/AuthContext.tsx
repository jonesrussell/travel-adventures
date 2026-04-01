import {
  createContext,
  createElement,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import type { ReactNode } from 'react';

import { queryClient } from '@/lib/query-client';
import {
  fetchCurrentUser,
  login as loginRequest,
  logout as logoutRequest,
  register as registerRequest,
} from '@/lib/api/auth-service';
import { getStoredToken } from '@/lib/api/token-storage';
import type { User } from '@/types/api';

type AuthContextValue = {
  user: User | null;
  isReady: boolean;
  signIn: (email: string, password: string) => Promise<void>;
  signUp: (email: string, password: string, name?: string) => Promise<void>;
  signOut: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      const token = await getStoredToken();
      if (!token) {
        if (!cancelled) setIsReady(true);
        return;
      }
      try {
        const u = await fetchCurrentUser();
        if (!cancelled) setUser(u);
      } catch {
        await logoutRequest();
        if (!cancelled) setUser(null);
      } finally {
        if (!cancelled) setIsReady(true);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  const signIn = useCallback(async (email: string, password: string) => {
    const { user: next } = await loginRequest(email, password);
    setUser(next);
    await queryClient.invalidateQueries();
  }, []);

  const signUp = useCallback(
    async (email: string, password: string, name?: string) => {
      const { user: next } = await registerRequest(email, password, name);
      setUser(next);
      await queryClient.invalidateQueries();
    },
    [],
  );

  const signOut = useCallback(async () => {
    await logoutRequest();
    setUser(null);
    queryClient.clear();
  }, []);

  const value = useMemo(
    () => ({ user, isReady, signIn, signUp, signOut }),
    [user, isReady, signIn, signUp, signOut],
  );

  return createElement(AuthContext.Provider, { value }, children);
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
