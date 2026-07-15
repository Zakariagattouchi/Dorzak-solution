import { create } from 'zustand';
import { authApi, platformApi } from '../api/endpoints';
import { advanceAuthSessionRevision, ApiError, getToken, setToken } from '../api/apiClient';
import { initialAccountInfo } from '../data/mockData';
import { useCartStore } from './cartStore';
import { useCustomerStore } from './customerStore';
import { invalidateMerchantScope } from './merchantScope';
import { useModalStore } from './modalStore';
import { useOrderStore } from './orderStore';
import { useProductStore } from './productStore';
import { useSettingsStore } from './settingsStore';
import { useToastStore } from './toastStore';

interface SessionUser {
  id: number;
  name: string;
  email: string;
  is_platform_admin: boolean;
}
interface SessionStore {
  id: number;
  name: string;
  currency: string;
  symbol_placement: string;
  language: string;
  country: string;
}

// Keys used to stash the operator's own session while impersonating a store.
const ADMIN_TOKEN_KEY = 'dorzak-admin-token';
const IMPERSONATING_KEY = 'dorzak-impersonating';
const readLocal = (k: string): string | null => {
  try {
    return localStorage.getItem(k);
  } catch {
    return null;
  }
};
const writeLocal = (k: string, v: string | null): void => {
  try {
    if (v === null) localStorage.removeItem(k);
    else localStorage.setItem(k, v);
  } catch {
    /* ignore */
  }
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
  register: (payload: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    business_name: string;
  }) => Promise<void>;
  logout: () => Promise<void>;
  impersonate: (storeId: number) => Promise<void>;
  stopImpersonating: () => Promise<boolean>;
  can: (ability: string) => boolean;
}

type AuthSetter = (partial: Partial<AuthState>) => void;
type AuthSnapshot = {
  status: AuthState['status'];
  storeId: number | null;
  token: string | null;
  userId: number | null;
};

let authAttemptEpoch = 0;

const beginAuthAttempt = (): number => {
  authAttemptEpoch += 1;
  return authAttemptEpoch;
};

const isCurrentAuthAttempt = (attempt: number): boolean => attempt === authAttemptEpoch;

const captureAuthSnapshot = (state: AuthState): AuthSnapshot => ({
  status: state.status,
  storeId: state.store?.id ?? null,
  token: getToken(),
  userId: state.user?.id ?? null,
});

const clearMerchantState = (): void => {
  invalidateMerchantScope();
  useProductStore.setState({ products: [], categories: [], loading: false, error: null });
  useCustomerStore.setState({ customers: [], loading: false, error: null });
  useOrderStore.setState({ orders: [], loading: false, error: null, unseenOrderIds: [] });
  useSettingsStore.setState({ accountInfo: { ...initialAccountInfo }, loading: false });
  useCartStore.getState().clearCart();
  useModalStore.getState().closeModal();
  for (const toast of [...useToastStore.getState().toasts]) {
    useToastStore.getState().removeToast(toast.id);
  }
};

const withholdMerchantSession = (set: AuthSetter): void => {
  set({
    user: null,
    store: null,
    role: null,
    abilities: [],
    status: 'loading',
    error: null,
  });
  clearMerchantState();
};

const publishGuestSession = (set: AuthSetter): void => {
  withholdMerchantSession(set);
  set({
    user: null,
    store: null,
    role: null,
    abilities: [],
    status: 'guest',
    error: null,
    impersonating: null,
  });
};

const isSessionReplacement = (previous: AuthSnapshot, data: any, token?: string): boolean =>
  previous.status !== 'authenticated' ||
  previous.userId !== (data.user?.id ?? null) ||
  previous.storeId !== (data.store?.id ?? null) ||
  (token !== undefined && previous.token !== token);

const publishAuthenticatedSession = (
  set: AuthSetter,
  previous: AuthSnapshot,
  data: any,
  token?: string,
  merchantStateAlreadyCleared = false,
): void => {
  const sessionReplacement = isSessionReplacement(previous, data, token);
  if (!merchantStateAlreadyCleared && sessionReplacement) {
    withholdMerchantSession(set);
  }
  if (token !== undefined) {
    setToken(token);
  } else if (sessionReplacement) {
    advanceAuthSessionRevision();
  }
  set({
    user: data.user,
    store: data.store,
    role: data.role,
    abilities: data.abilities,
    status: 'authenticated',
    error: null,
  });
};

const hydrateSession = async (
  set: AuthSetter,
  get: () => AuthState,
  attempt: number,
  previous: AuthSnapshot,
  merchantStateAlreadyCleared = false,
): Promise<void> => {
  if (!isCurrentAuthAttempt(attempt)) return;
  if (!getToken()) {
    publishGuestSession(set);
    return;
  }

  set({ status: 'loading', error: null });
  try {
    const { data } = (await authApi.me()) as any;
    if (!isCurrentAuthAttempt(attempt)) return;
    publishAuthenticatedSession(set, previous, data, undefined, merchantStateAlreadyCleared);
  } catch {
    if (!isCurrentAuthAttempt(attempt)) return;
    setToken(null);
    writeLocal(ADMIN_TOKEN_KEY, null);
    writeLocal(IMPERSONATING_KEY, null);
    publishGuestSession(set);
  }
};

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  store: null,
  role: null,
  abilities: [],
  status: 'idle',
  error: null,
  impersonating: readLocal(IMPERSONATING_KEY),

  bootstrap: async () => {
    const attempt = beginAuthAttempt();
    const previous = captureAuthSnapshot(get());
    await hydrateSession(set, get, attempt, previous);
  },

  login: async (email, password) => {
    const attempt = beginAuthAttempt();
    const previous = captureAuthSnapshot(get());
    set({ error: null });
    try {
      const { data } = (await authApi.login(email, password)) as any;
      if (!isCurrentAuthAttempt(attempt)) return;
      publishAuthenticatedSession(set, previous, data, data.token);
    } catch (e) {
      if (!isCurrentAuthAttempt(attempt)) throw e;
      const err = e as ApiError;
      set({ error: err.errors?.email?.[0] ?? err.message ?? 'Login failed' });
      throw e;
    }
  },

  register: async (payload) => {
    const attempt = beginAuthAttempt();
    const previous = captureAuthSnapshot(get());
    set({ error: null });
    try {
      const { data } = (await authApi.register(payload)) as any;
      if (!isCurrentAuthAttempt(attempt)) return;
      publishAuthenticatedSession(set, previous, data, data.token);
    } catch (e) {
      if (!isCurrentAuthAttempt(attempt)) throw e;
      const err = e as ApiError;
      const firstError = err.errors ? Object.values(err.errors)[0]?.[0] : undefined;
      set({ error: firstError ?? err.message ?? 'Signup failed' });
      throw e;
    }
  },

  logout: async () => {
    beginAuthAttempt();
    let logoutRequest: Promise<unknown>;
    try {
      logoutRequest = authApi.logout();
    } catch (error) {
      logoutRequest = Promise.reject(error);
    }
    setToken(null);
    writeLocal(ADMIN_TOKEN_KEY, null);
    writeLocal(IMPERSONATING_KEY, null);
    publishGuestSession(set);
    try {
      await logoutRequest;
    } catch {
      /* ignore */
    }
  },

  // Enter a store's back office as its owner. Stash the operator's own token so
  // stopImpersonating() can restore it without re-authenticating.
  impersonate: async (storeId) => {
    const attempt = beginAuthAttempt();
    const adminToken = getToken();
    const { data } = (await platformApi.stores.impersonate(storeId)) as any;
    if (!isCurrentAuthAttempt(attempt)) return;
    withholdMerchantSession(set);
    writeLocal(ADMIN_TOKEN_KEY, adminToken);
    writeLocal(IMPERSONATING_KEY, data.store.name);
    setToken(data.token);
    set({ impersonating: data.store.name });
    await hydrateSession(set, get, attempt, captureAuthSnapshot(get()), true);
  },

  stopImpersonating: async () => {
    const attempt = beginAuthAttempt();
    try {
      await authApi.endImpersonation();
    } catch (error) {
      if (!isCurrentAuthAttempt(attempt)) return false;
      throw error;
    }
    if (!isCurrentAuthAttempt(attempt)) return false;

    const adminToken = readLocal(ADMIN_TOKEN_KEY);
    withholdMerchantSession(set);
    setToken(adminToken);
    writeLocal(ADMIN_TOKEN_KEY, null);
    writeLocal(IMPERSONATING_KEY, null);
    set({ impersonating: null });
    await hydrateSession(set, get, attempt, captureAuthSnapshot(get()), true);

    return (
      isCurrentAuthAttempt(attempt) &&
      get().status === 'authenticated' &&
      get().user?.is_platform_admin === true
    );
  },

  can: (ability) => {
    if (ability === 'platform.admin') return get().user?.is_platform_admin === true;
    return get().abilities.includes(ability);
  },
}));
