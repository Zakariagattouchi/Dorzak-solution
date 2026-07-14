import { beforeEach, expect, test, vi } from 'vitest';
import { setToken } from '../api/apiClient';
import { authApi } from '../api/endpoints';
import { useAuthStore } from './authStore';

vi.mock('../api/endpoints', () => ({
  authApi: { login: vi.fn(), logout: vi.fn(), me: vi.fn(), register: vi.fn() },
  platformApi: { stores: { impersonate: vi.fn() } },
}));

beforeEach(() => {
  localStorage.clear();
  vi.clearAllMocks();
  setToken(null);
  useAuthStore.setState({
    user: null,
    store: null,
    role: null,
    abilities: [],
    status: 'idle',
    error: null,
    impersonating: null,
  });
});

test('bootstraps guest/authenticated sessions and exposes login validation', async () => {
  await useAuthStore.getState().bootstrap();
  expect(useAuthStore.getState().status).toBe('guest');

  const user = { id: 1, name: 'Owner', email: 'owner@e2e.dorzak.test', is_platform_admin: false };
  const store = { id: 1, name: 'Dorzak', currency: 'QAR', symbol_placement: 'BEFORE', language: 'en', country: 'Qatar' };
  setToken('valid');
  vi.mocked(authApi.me).mockResolvedValue({
    data: { user, store, role: 'OWNER', abilities: ['orders.create'] },
  });
  await useAuthStore.getState().bootstrap();
  expect(useAuthStore.getState()).toMatchObject({
    user,
    store,
    status: 'authenticated',
    role: 'OWNER',
    abilities: ['orders.create'],
  });

  vi.mocked(authApi.login).mockRejectedValue({
    status: 422,
    message: 'Invalid',
    errors: { email: ['Bad credentials'] },
  });
  await expect(useAuthStore.getState().login('bad@example.test', 'bad')).rejects.toBeTruthy();
  expect(useAuthStore.getState().error).toBe('Bad credentials');
});
