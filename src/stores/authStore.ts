import { create } from 'zustand';
import { authApi, platformApi } from '../api/endpoints';
import { ApiError, getToken, setToken } from '../api/apiClient';

interface SessionUser { id: number; name: string; email: string; is_platform_admin: boolean; }
interface SessionStore { id: number; name: string; currency: string; symbol_placement: string; language: string; country: string; }

// Keys used to stash the operator's own session while impersonating a store.
const ADMIN_TOKEN_KEY = 'dorzak-admin-token';
const IMPERSONATING_KEY = 'dorzak-impersonating';
const readLocal = (k: string): string | null => { try { return localStorage.getItem(k); } catch { return null; } };
const writeLocal = (k: string, v: string | null): void => {
  try { if (v === null) localStorage.removeItem(k); else localStorage.setItem(k, v); } catch { /* ignore */ }
};

interface AuthState {
  user: SessionUser | null;
  store: SessionStore | null;
  role: string | null;
  abilities: string[];
  status: 'idle' | 'loading' | 'authenticated' | 'guest';
  error: string | null;
  impersonating: string | null;
  bootstrap: () => Promise<void>;
  login: (email: string, password: string) => Promise<void>;
  register: (payload: { name: string; email: string; password: string; password_confirmation: string; business_name: string }) => Promise<void>;
  logout: () => Promise<void>;
  impersonate: (storeId: number) => Promise<void>;
  stopImpersonating: () => Promise<void>;
  can: (ability: string) => boolean;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  store: null,
  role: null,
  abilities: [],
  status: 'idle',
  error: null,
  impersonating: readLocal(IMPERSONATING_KEY),

  bootstrap: async () => {
    if (!getToken()) {
      set({ status: 'guest' });
      return;
    }
    set({ status: 'loading' });
    try {
      const { data } = (await authApi.me()) as any;
      set({ user: data.user, store: data.store, role: data.role, abilities: data.abilities, status: 'authenticated', error: null });
    } catch {
      setToken(null);
      set({ status: 'guest', user: null, store: null, role: null, abilities: [] });
    }
  },

  login: async (email, password) => {
    set({ error: null });
    try {
      const { data } = (await authApi.login(email, password)) as any;
      setToken(data.token);
      set({ user: data.user, store: data.store, role: data.role, abilities: data.abilities, status: 'authenticated' });
    } catch (e) {
      const err = e as ApiError;
      set({ error: err.errors?.email?.[0] ?? err.message ?? 'Login failed' });
      throw e;
    }
  },

  register: async (payload) => {
    set({ error: null });
    try {
      const { data } = (await authApi.register(payload)) as any;
      setToken(data.token);
      set({ user: data.user, store: data.store, role: data.role, abilities: data.abilities, status: 'authenticated' });
    } catch (e) {
      const err = e as ApiError;
      const firstError = err.errors ? Object.values(err.errors)[0]?.[0] : undefined;
      set({ error: firstError ?? err.message ?? 'Signup failed' });
      throw e;
    }
  },

  logout: async () => {
    try { await authApi.logout(); } catch { /* ignore */ }
    setToken(null);
    writeLocal(ADMIN_TOKEN_KEY, null);
    writeLocal(IMPERSONATING_KEY, null);
    set({ user: null, store: null, role: null, abilities: [], status: 'guest', impersonating: null });
  },

  // Enter a store's back office as its owner. Stash the operator's own token so
  // stopImpersonating() can restore it without re-authenticating.
  impersonate: async (storeId) => {
    const { data } = (await platformApi.stores.impersonate(storeId)) as any;
    writeLocal(ADMIN_TOKEN_KEY, getToken());
    writeLocal(IMPERSONATING_KEY, data.store.name);
    setToken(data.token);
    set({ impersonating: data.store.name });
    await get().bootstrap();
  },

  stopImpersonating: async () => {
    const adminToken = readLocal(ADMIN_TOKEN_KEY);
    setToken(adminToken);
    writeLocal(ADMIN_TOKEN_KEY, null);
    writeLocal(IMPERSONATING_KEY, null);
    set({ impersonating: null });
    await get().bootstrap();
  },

  can: (ability) => {
    if (ability === 'platform.admin') return get().user?.is_platform_admin === true;
    return get().abilities.includes(ability);
  },
}));
