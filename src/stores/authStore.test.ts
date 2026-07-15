import { beforeEach, expect, test, vi } from 'vitest';
import { getToken, request, setToken } from '../api/apiClient';
import { authApi, catalogApi, customerApi, orderApi, settingsApi } from '../api/endpoints';
import { initialAccountInfo } from '../data/mockData';
import { useAuthStore } from './authStore';
import { useCartStore } from './cartStore';
import { useCustomerStore } from './customerStore';
import { useModalStore } from './modalStore';
import { useOrderStore } from './orderStore';
import { useProductStore } from './productStore';
import { useSettingsStore } from './settingsStore';
import { useToastStore } from './toastStore';

const endpointDoubles = vi.hoisted(() => ({
  authApi: { login: vi.fn(), logout: vi.fn(), me: vi.fn(), register: vi.fn() },
  catalogApi: {
    categories: vi.fn(),
    createCategory: vi.fn(),
    createProduct: vi.fn(),
    deleteProduct: vi.fn(),
    deleteProductAdditionalImage: vi.fn(),
    product: vi.fn(),
    products: vi.fn(),
    updateCategory: vi.fn(),
    updateProduct: vi.fn(),
    uploadCategoryImage: vi.fn(),
    uploadProductAdditionalImage: vi.fn(),
    uploadProductImage: vi.fn(),
  },
  customerApi: { create: vi.fn(), list: vi.fn(), remove: vi.fn(), update: vi.fn() },
  orderApi: {
    create: vi.fn(),
    list: vi.fn(),
    updatePaymentStatus: vi.fn(),
    updateStatus: vi.fn(),
  },
  platformApi: { stores: { impersonate: vi.fn() } },
  settingsApi: { get: vi.fn(), update: vi.fn() },
}));

vi.mock('../api/endpoints', () => endpointDoubles);

function deferred<T>() {
  let resolve!: (value: T | PromiseLike<T>) => void;
  let reject!: (reason?: unknown) => void;
  const promise = new Promise<T>((resolvePromise, rejectPromise) => {
    resolve = resolvePromise;
    reject = rejectPromise;
  });

  return { promise, reject, resolve };
}

function session(id: number, token: string) {
  return {
    data: {
      abilities: ['orders.create'],
      role: 'OWNER',
      store: {
        country: 'Qatar',
        currency: 'QAR',
        id,
        language: 'en',
        name: `Store ${id}`,
        symbol_placement: 'BEFORE',
      },
      token,
      user: {
        email: `owner-${id}@example.test`,
        id,
        is_platform_admin: false,
        name: `Owner ${id}`,
      },
    },
  };
}

function seedMerchantState(label: string) {
  const product = {
    id: `${label}-product`,
    name: `${label} product`,
    price: 10,
  } as any;
  const customer = {
    id: `${label}-customer`,
    name: `${label} customer`,
  } as any;
  useProductStore.setState({
    categories: [{ id: `${label}-category`, name: `${label} category` } as any],
    error: `${label} product error`,
    loading: true,
    products: [product],
  });
  useCustomerStore.setState({
    customers: [customer],
    error: `${label} customer error`,
    loading: true,
  });
  useOrderStore.setState({
    error: `${label} order error`,
    loading: true,
    orders: [{ id: `${label}-order` } as any],
    unseenOrderIds: [`${label}-order`],
  });
  useSettingsStore.setState({
    accountInfo: { ...initialAccountInfo, businessName: `${label} settings` },
    loading: true,
  });
  useCartStore.setState({
    discount: 3,
    items: [{ lineKey: label, product, quantity: 1 } as any],
    selectedCustomer: customer,
  });
  useModalStore.getState().openModal('PRODUCT_EDIT', { id: label });
  useToastStore.setState({
    toasts: [{ id: label, message: `${label} toast`, type: 'warning' }],
  });
}

function expectMerchantStateCleared() {
  expect(useProductStore.getState()).toMatchObject({
    categories: [],
    error: null,
    loading: false,
    products: [],
  });
  expect(useCustomerStore.getState()).toMatchObject({
    customers: [],
    error: null,
    loading: false,
  });
  expect(useOrderStore.getState()).toMatchObject({
    error: null,
    loading: false,
    orders: [],
    unseenOrderIds: [],
  });
  expect(useSettingsStore.getState()).toMatchObject({
    accountInfo: initialAccountInfo,
    loading: false,
  });
  expect(useCartStore.getState()).toMatchObject({
    discount: 0,
    items: [],
    selectedCustomer: null,
  });
  expect(useModalStore.getState()).toMatchObject({ activeModal: null, payload: null });
  expect(useToastStore.getState().toasts).toEqual([]);
}

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
  useProductStore.setState({ products: [], categories: [], loading: false, error: null });
  useCustomerStore.setState({ customers: [], loading: false, error: null });
  useOrderStore.setState({ orders: [], loading: false, error: null, unseenOrderIds: [] });
  useSettingsStore.setState({ accountInfo: { ...initialAccountInfo }, loading: false });
  useCartStore.getState().clearCart();
  useModalStore.getState().closeModal();
  for (const toast of useToastStore.getState().toasts) {
    useToastStore.getState().removeToast(toast.id);
  }
  document.documentElement.lang = 'en';
  document.documentElement.dir = 'ltr';
});

test('bootstraps guest/authenticated sessions and exposes login validation', async () => {
  await useAuthStore.getState().bootstrap();
  expect(useAuthStore.getState().status).toBe('guest');

  const user = { id: 1, name: 'Owner', email: 'owner@e2e.dorzak.test', is_platform_admin: false };
  const store = {
    id: 1,
    name: 'Dorzak',
    currency: 'QAR',
    symbol_placement: 'BEFORE',
    language: 'en',
    country: 'Qatar',
  };
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

test('isolates merchant state and suppresses stale auth and tenant request completions', async () => {
  const clearCart = vi.spyOn(useCartStore.getState(), 'clearCart');
  const closeModal = vi.spyOn(useModalStore.getState(), 'closeModal');
  const removeToast = vi.spyOn(useToastStore.getState(), 'removeToast');
  const merchantA = session(1, 'token-a');
  setToken(merchantA.data.token);
  useAuthStore.setState({
    abilities: merchantA.data.abilities,
    role: merchantA.data.role,
    status: 'authenticated',
    store: merchantA.data.store,
    user: merchantA.data.user,
  });
  seedMerchantState('merchant-a');

  const merchantB = session(2, 'token-b');
  vi.mocked(authApi.login).mockResolvedValueOnce(merchantB);
  await useAuthStore.getState().login('owner-2@example.test', 'password');
  expect(useAuthStore.getState()).toMatchObject({
    error: null,
    status: 'authenticated',
    store: merchantB.data.store,
    user: merchantB.data.user,
  });
  expectMerchantStateCleared();
  expect(clearCart).toHaveBeenCalledTimes(1);
  expect(closeModal).toHaveBeenCalledTimes(1);
  expect(removeToast).toHaveBeenCalledWith('merchant-a');

  seedMerchantState('logout');
  const logoutResponse = deferred<unknown>();
  vi.mocked(authApi.logout).mockReturnValueOnce(logoutResponse.promise);
  const pendingLogout = useAuthStore.getState().logout();
  expect(useAuthStore.getState()).toMatchObject({
    abilities: [],
    role: null,
    status: 'guest',
    store: null,
    user: null,
  });
  expectMerchantStateCleared();
  expect(clearCart).toHaveBeenCalledTimes(2);
  expect(closeModal).toHaveBeenCalledTimes(2);
  expect(removeToast).toHaveBeenCalledWith('logout');

  const merchantC = session(3, 'token-c');
  vi.mocked(authApi.login).mockResolvedValueOnce(merchantC);
  await useAuthStore.getState().login('owner-3@example.test', 'password');
  logoutResponse.resolve(undefined);
  await pendingLogout;
  expect(useAuthStore.getState()).toMatchObject({
    status: 'authenticated',
    store: merchantC.data.store,
  });

  const staleSuccess = deferred<any>();
  vi.mocked(authApi.login).mockReturnValueOnce(staleSuccess.promise);
  const pendingStaleSuccess = useAuthStore.getState().login('stale@example.test', 'password');
  const merchantD = session(4, 'token-d');
  vi.mocked(authApi.login).mockResolvedValueOnce(merchantD);
  await useAuthStore.getState().login('owner-4@example.test', 'password');
  seedMerchantState('merchant-d-current');
  staleSuccess.resolve(session(99, 'stale-token'));
  await pendingStaleSuccess;
  expect(useAuthStore.getState()).toMatchObject({
    error: null,
    store: merchantD.data.store,
  });
  expect(localStorage.getItem('dorzak-token')).toBe('token-d');
  expect(useProductStore.getState().products[0]?.id).toBe('merchant-d-current-product');
  expect(useModalStore.getState().payload).toEqual({ id: 'merchant-d-current' });
  expect(useToastStore.getState().toasts).toMatchObject([{ id: 'merchant-d-current' }]);

  const staleError = deferred<any>();
  vi.mocked(authApi.login).mockReturnValueOnce(staleError.promise);
  const pendingStaleError = useAuthStore.getState().login('stale-error@example.test', 'password');
  const merchantE = session(5, 'token-e');
  vi.mocked(authApi.login).mockResolvedValueOnce(merchantE);
  await useAuthStore.getState().login('owner-5@example.test', 'password');
  staleError.reject({ message: 'Stale login error', status: 422 });
  await expect(pendingStaleError).rejects.toMatchObject({ message: 'Stale login error' });
  expect(useAuthStore.getState()).toMatchObject({ error: null, store: merchantE.data.store });
  expect(localStorage.getItem('dorzak-token')).toBe('token-e');

  const staleBootstrap = deferred<any>();
  vi.mocked(authApi.me).mockReturnValueOnce(staleBootstrap.promise);
  const pendingBootstrap = useAuthStore.getState().bootstrap();
  const merchantF = session(6, 'token-f');
  vi.mocked(authApi.login).mockResolvedValueOnce(merchantF);
  await useAuthStore.getState().login('owner-6@example.test', 'password');
  seedMerchantState('merchant-f-current');
  staleBootstrap.reject({ message: 'Stale bootstrap error', status: 401 });
  await pendingBootstrap;
  expect(useAuthStore.getState()).toMatchObject({
    error: null,
    status: 'authenticated',
    store: merchantF.data.store,
  });
  expect(localStorage.getItem('dorzak-token')).toBe('token-f');
  expect(useProductStore.getState().products[0]?.id).toBe('merchant-f-current-product');
  expect(useModalStore.getState().payload).toEqual({ id: 'merchant-f-current' });
  expect(useToastStore.getState().toasts).toMatchObject([{ id: 'merchant-f-current' }]);

  const staleUnauthorizedResponse = deferred<Response>();
  const fetchSpy = vi
    .spyOn(globalThis, 'fetch')
    .mockReturnValueOnce(staleUnauthorizedResponse.promise);
  const staleMerchantRequest = request('/orders');
  const sameTokenMerchant = session(7, 'token-f');
  vi.mocked(authApi.me).mockResolvedValueOnce(sameTokenMerchant);
  await useAuthStore.getState().bootstrap();
  staleUnauthorizedResponse.resolve(new Response(null, { status: 401 }));
  await expect(staleMerchantRequest).rejects.toMatchObject({ status: 401 });
  expect(useAuthStore.getState()).toMatchObject({
    status: 'authenticated',
    store: sameTokenMerchant.data.store,
  });
  expect(getToken()).toBe('token-f');
  fetchSpy.mockRestore();

  seedMerchantState('late');
  const productsResponse = deferred<any>();
  const categoriesResponse = deferred<any>();
  const customersResponse = deferred<any>();
  const ordersResponse = deferred<any>();
  const settingsResponse = deferred<any>();
  vi.mocked(catalogApi.products).mockReturnValueOnce(productsResponse.promise);
  vi.mocked(catalogApi.categories).mockReturnValueOnce(categoriesResponse.promise);
  vi.mocked(customerApi.list).mockReturnValueOnce(customersResponse.promise);
  vi.mocked(orderApi.list).mockReturnValueOnce(ordersResponse.promise);
  vi.mocked(settingsApi.get).mockReturnValueOnce(settingsResponse.promise);
  const lateRequests = [
    useProductStore.getState().fetchProducts(),
    useProductStore.getState().fetchCategories(),
    useCustomerStore.getState().fetchCustomers(),
    useOrderStore.getState().fetchOrders(),
    useSettingsStore.getState().fetchSettings(),
  ];

  const merchantG = session(7, 'token-g');
  vi.mocked(authApi.login).mockResolvedValueOnce(merchantG);
  await useAuthStore.getState().login('owner-7@example.test', 'password');
  useProductStore.setState({ loading: true });
  useCustomerStore.setState({ loading: true });
  useOrderStore.setState({ loading: true });
  useSettingsStore.setState({
    accountInfo: { ...initialAccountInfo, businessName: 'Merchant G current', language: 'en' },
    loading: true,
  });
  document.documentElement.lang = 'en';
  document.documentElement.dir = 'ltr';
  productsResponse.resolve({ data: [{ id: 91, name: 'Stale product' }] });
  categoriesResponse.resolve({ data: [{ id: 94, name: 'Stale category' }] });
  customersResponse.reject({ message: 'Stale customer failure' });
  ordersResponse.resolve({
    data: [{ id: 92, order_number: 'STALE-ORDER', status: 'COMPLETE' }],
  });
  settingsResponse.resolve({
    data: { general: { business_name: 'Stale merchant settings', language: 'ar' } },
  });
  await Promise.all(lateRequests);
  expect(useProductStore.getState()).toMatchObject({
    categories: [],
    error: null,
    loading: true,
    products: [],
  });
  expect(useCustomerStore.getState()).toMatchObject({
    customers: [],
    error: null,
    loading: true,
  });
  expect(useOrderStore.getState()).toMatchObject({
    error: null,
    loading: true,
    orders: [],
    unseenOrderIds: [],
  });
  expect(useSettingsStore.getState()).toMatchObject({
    accountInfo: { businessName: 'Merchant G current', language: 'en' },
    loading: true,
  });
  expect(document.documentElement).toHaveAttribute('lang', 'en');
  expect(document.documentElement).toHaveAttribute('dir', 'ltr');

  const createProductResponse = deferred<any>();
  const firstSettingsUpdate = deferred<any>();
  vi.mocked(catalogApi.createProduct).mockReturnValueOnce(createProductResponse.promise);
  vi.mocked(settingsApi.update).mockReturnValueOnce(firstSettingsUpdate.promise);
  const pendingProduct = useProductStore.getState().createProduct(
    {
      category: '',
      code: 'SCOPE',
      cost: 5,
      isFeatured: false,
      minStock: 0,
      name: 'Scoped product',
      price: 10,
      showInOnlineStore: true,
      stock: 1,
      taxable: true,
      trackStock: true,
      unit: 'pcs',
      variantGroups: [],
      variants: [],
    },
    new File(['image'], 'product.png', { type: 'image/png' }),
  );
  const pendingSettings = useSettingsStore
    .getState()
    .updateSettings({ businessName: 'Old merchant', taxRate: 7 });

  const merchantH = session(8, 'token-h');
  vi.mocked(authApi.login).mockResolvedValueOnce(merchantH);
  await useAuthStore.getState().login('owner-8@example.test', 'password');
  createProductResponse.resolve({ data: { id: 93, name: 'Scoped product' } });
  firstSettingsUpdate.resolve({ data: { general: { business_name: 'Old merchant' } } });
  await expect(pendingProduct).rejects.toThrow('Merchant session changed');
  await expect(pendingSettings).rejects.toThrow('Merchant session changed');
  expect(catalogApi.uploadProductImage).not.toHaveBeenCalled();
  expect(catalogApi.product).not.toHaveBeenCalled();
  expect(settingsApi.update).toHaveBeenCalledTimes(1);
  expectMerchantStateCleared();
});
