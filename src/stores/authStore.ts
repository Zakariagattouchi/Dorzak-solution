import { create } from 'zustand';
import { authApi } from '../api/endpoints';
import { ApiError, getToken, setToken } from '../api/apiClient';

interface SessionUser { id: number; name: string; email: string; is_platform_admin: boolean; }
interface SessionStore { id: number; name: string; currency: string; symbol_placement: string; language: string; country: string; }

interface AuthState {
  user: SessionUser | null;
  store: SessionStore | null;
  role: string | null;
  abilities: string[];
  status: 'idle' | 'loading' | 'authenticated' | 'guest';
  error: string | null;
  bootstrap: () => Promise<void>;
  login: (email: string, password: string) => Promise<void>;
  register: (payload: { name: string; email: string; password: string; password_confirmation: string; business_name: string }) => Promise<void>;
  logout: () => Promise<void>;
  can: (ability: string) => boolean;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  store: null,
  role: null,
  abilities: [],
  status: 'idle',
  error: null,

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
    set({ user: null, store: null, role: null, abilities: [], status: 'guest' });
  },

  can: (ability) => {
    if (ability === 'platform.admin') return get().user?.is_platform_admin === true;
    return get().abilities.includes(ability);
  },
}));
