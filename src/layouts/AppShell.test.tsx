import { act, render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { beforeEach, expect, test, vi } from 'vitest';
import { AppShell } from './AppShell';

const doubles = vi.hoisted(() => ({
  auth: {
    status: 'idle' as 'idle' | 'loading' | 'guest' | 'authenticated',
    store: null as null | { id: number; name: string },
    user: null as null | { is_platform_admin: boolean },
  },
  bootstrap: vi.fn(),
  fetchSettings: vi.fn(),
  fetchProducts: vi.fn(),
  fetchCategories: vi.fn(),
  fetchCustomers: vi.fn(),
  fetchOrders: vi.fn(),
}));

vi.mock('../stores/authStore', () => ({
  useAuthStore: () => ({ ...doubles.auth, bootstrap: doubles.bootstrap }),
}));
vi.mock('../stores/productStore', () => ({
  useProductStore: () => ({
    fetchProducts: doubles.fetchProducts,
    fetchCategories: doubles.fetchCategories,
  }),
}));
vi.mock('../stores/customerStore', () => ({
  useCustomerStore: () => ({ fetchCustomers: doubles.fetchCustomers }),
}));
vi.mock('../stores/orderStore', () => ({
  useOrderStore: () => ({ fetchOrders: doubles.fetchOrders }),
}));
vi.mock('../stores/settingsStore', () => ({
  useSettingsStore: () => ({ fetchSettings: doubles.fetchSettings }),
}));
vi.mock('../hooks/useOrderPolling', () => ({ useOrderPolling: vi.fn() }));
vi.mock('../components/navigation/Sidebar', () => ({ Sidebar: () => null }));
vi.mock('../components/navigation/Topbar', () => ({ Topbar: () => null }));
vi.mock('../components/navigation/ImpersonationBanner', () => ({
  ImpersonationBanner: () => null,
}));
vi.mock('../components/modals/ModalHost', () => ({ ModalHost: () => null }));
vi.mock('../components/feedback/ToastHost', () => ({ ToastHost: () => null }));

function renderShell() {
  return render(shellRoutes());
}

function shellRoutes() {
  return (
    <MemoryRouter initialEntries={['/']}>
      <Routes>
        <Route path="/" element={<AppShell />}>
          <Route index element={<h1>Merchant route</h1>} />
        </Route>
        <Route path="/login" element={<h1>Login route</h1>} />
        <Route path="/platform" element={<h1>Platform route</h1>} />
      </Routes>
    </MemoryRouter>
  );
}

function deferred<T>() {
  let resolve!: (value: T | PromiseLike<T>) => void;
  const promise = new Promise<T>((resolvePromise) => {
    resolve = resolvePromise;
  });

  return { promise, resolve };
}

beforeEach(() => {
  vi.clearAllMocks();
  doubles.auth.status = 'idle';
  doubles.auth.store = null;
  doubles.auth.user = null;
});

test('guards shell states and hydrates merchant data with settings first', async () => {
  let view = renderShell();
  expect(screen.getByRole('status', { name: 'Loading your store…' })).toBeInTheDocument();
  await waitFor(() => expect(doubles.bootstrap).toHaveBeenCalledTimes(1));
  view.unmount();

  doubles.auth.status = 'guest';
  view = renderShell();
  expect(await screen.findByRole('heading', { name: 'Login route' })).toBeInTheDocument();
  view.unmount();

  doubles.auth.status = 'authenticated';
  doubles.auth.user = { is_platform_admin: true };
  doubles.auth.store = null;
  view = renderShell();
  expect(await screen.findByRole('heading', { name: 'Platform route' })).toBeInTheDocument();
  view.unmount();

  doubles.auth.user = { is_platform_admin: false };
  doubles.auth.store = { id: 1, name: 'Dorzak' };
  const settings = deferred<void>();
  doubles.fetchSettings.mockReturnValueOnce(settings.promise);
  renderShell();
  expect(await screen.findByRole('heading', { name: 'Merchant route' })).toBeInTheDocument();
  await waitFor(() => expect(doubles.fetchSettings).toHaveBeenCalledTimes(1));
  for (const fetchDomain of [
    doubles.fetchProducts,
    doubles.fetchCategories,
    doubles.fetchCustomers,
    doubles.fetchOrders,
  ]) {
    expect(fetchDomain).not.toHaveBeenCalled();
  }

  settings.resolve();
  await waitFor(() => {
    expect(doubles.fetchProducts).toHaveBeenCalledTimes(1);
    expect(doubles.fetchCategories).toHaveBeenCalledTimes(1);
    expect(doubles.fetchCustomers).toHaveBeenCalledTimes(1);
    expect(doubles.fetchOrders).toHaveBeenCalledTimes(1);
  });
});

test('cancels deferred merchant hydration after unmount or auth-state exit', async () => {
  doubles.auth.status = 'authenticated';
  doubles.auth.user = { is_platform_admin: false };
  doubles.auth.store = { id: 1, name: 'Dorzak' };

  const unmountedSettings = deferred<void>();
  doubles.fetchSettings.mockReturnValueOnce(unmountedSettings.promise);
  const unmountedView = renderShell();
  await waitFor(() => expect(doubles.fetchSettings).toHaveBeenCalledTimes(1));
  unmountedView.unmount();
  await act(async () => {
    unmountedSettings.resolve();
    await unmountedSettings.promise;
  });

  for (const fetchDomain of [
    doubles.fetchProducts,
    doubles.fetchCategories,
    doubles.fetchCustomers,
    doubles.fetchOrders,
  ]) {
    expect(fetchDomain).not.toHaveBeenCalled();
  }

  vi.clearAllMocks();
  const exitedSettings = deferred<void>();
  doubles.fetchSettings.mockReturnValueOnce(exitedSettings.promise);
  const exitedView = renderShell();
  await waitFor(() => expect(doubles.fetchSettings).toHaveBeenCalledTimes(1));
  doubles.auth.status = 'guest';
  exitedView.rerender(shellRoutes());
  expect(await screen.findByRole('heading', { name: 'Login route' })).toBeInTheDocument();
  await act(async () => {
    exitedSettings.resolve();
    await exitedSettings.promise;
  });

  for (const fetchDomain of [
    doubles.fetchProducts,
    doubles.fetchCategories,
    doubles.fetchCustomers,
    doubles.fetchOrders,
  ]) {
    expect(fetchDomain).not.toHaveBeenCalled();
  }
});
