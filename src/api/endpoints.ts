// Typed endpoint functions grouped by module. Each Zustand store swaps its
// `mockApi.*` calls for the matching function here. Contracts:
// docs/backend-planning/05-api-contracts.md.

import { downloadFile, request, requestBlob } from './apiClient';

const PUBLIC_BASE = ((import.meta as any).env?.VITE_API_URL || '/api/v1')
  .replace(/\/api\/v1$/, '/api/public');

export const authApi = {
  login: (email: string, password: string) => request('/auth/login', { method: 'POST', body: { email, password, device_name: 'web' } }),
  logout: () => request('/auth/logout', { method: 'POST' }),
  me: () => request('/auth/me'),
  register: (payload: { name: string; email: string; password: string; password_confirmation: string; business_name: string }) =>
    request('/auth/register', { method: 'POST', body: { ...payload, device_name: 'web' } }),
  forgotPassword: (email: string) => request('/auth/forgot-password', { method: 'POST', body: { email } }),
};

export const settingsApi = {
  get: () => request('/settings'),
  update: (group: string, payload: Record<string, unknown>) => request(`/settings/${group}`, { method: 'PUT', body: payload }),
  uploadStorefront: (kind: 'banner' | 'logo', file: File) => {
    const fd = new FormData();
    fd.append('file', file);
    return request(`/settings/storefront/${kind}`, { method: 'POST', body: fd });
  },
};

export const staffApi = {
  list: () => request('/staff'),
  invite: (payload: { name: string; email: string; role: string }) => request('/staff/invitations', { method: 'POST', body: payload }),
  update: (userId: number, payload: { role?: string; is_active?: boolean }) => request(`/staff/${userId}`, { method: 'PATCH', body: payload }),
  remove: (userId: number) => request(`/staff/${userId}`, { method: 'DELETE' }),
  acceptInvitation: (token: string, payload: { password: string; password_confirmation: string; name?: string }) =>
    request(`/staff/invitations/${token}/accept`, { method: 'POST', body: payload }),
};

export const catalogApi = {
  categories: () => request('/categories'),
  createCategory: (payload: { name: string; color?: string; description?: string }) => request('/categories', { method: 'POST', body: payload }),
  updateCategory: (id: number, payload: Record<string, unknown>) => request(`/categories/${id}`, { method: 'PUT', body: payload }),
  deleteCategory: (id: number) => request(`/categories/${id}`, { method: 'DELETE' }),
  reorderCategories: (ids: number[]) => request('/categories/reorder', { method: 'PATCH', body: { ids } }),
  uploadCategoryImage: (id: number, file: File) => {
    const fd = new FormData();
    fd.append('file', file);
    return request(`/categories/${id}/image`, { method: 'POST', body: fd });
  },

  products: (params?: Record<string, string | number>) => request('/products', { params }),
  product: (id: number) => request(`/products/${id}`),
  createProduct: (payload: Record<string, unknown>) => request('/products', { method: 'POST', body: payload }),
  updateProduct: (id: number, payload: Record<string, unknown>) => request(`/products/${id}`, { method: 'PUT', body: payload }),
  deleteProduct: (id: number) => request(`/products/${id}`, { method: 'DELETE' }),
  uploadProductImage: (id: number, file: File) => {
    const fd = new FormData();
    fd.append('file', file);
    return request(`/products/${id}/image`, { method: 'POST', body: fd });
  },
  uploadProductAdditionalImage: (id: number, file: File) => {
    const fd = new FormData();
    fd.append('file', file);
    return request(`/products/${id}/additional-images`, { method: 'POST', body: fd });
  },
  deleteProductAdditionalImage: (id: number, path: string) => {
    return request(`/products/${id}/additional-images`, { method: 'DELETE', body: { path } });
  },
  translateArabic: (payload: { name: string; description?: string }) =>
    request('/products/translate-arabic', { method: 'POST', body: payload }),
};

export const customerApi = {
  list: (params?: Record<string, string | number>) => request('/customers', { params }),
  get: (id: number) => request(`/customers/${id}`),
  create: (payload: Record<string, unknown>) => request('/customers', { method: 'POST', body: payload }),
  update: (id: number, payload: Record<string, unknown>) => request(`/customers/${id}`, { method: 'PUT', body: payload }),
  remove: (id: number) => request(`/customers/${id}`, { method: 'DELETE' }),
  import: (file: File) => {
    const fd = new FormData();
    fd.append('file', file);
    return request('/customers/import', { method: 'POST', body: fd });
  },
};

export const orderApi = {
  list: (params?: Record<string, string | number>) => request('/orders', { params }),
  get: (id: number) => request(`/orders/${id}`),
  create: (payload: Record<string, unknown>) => request('/orders', { method: 'POST', body: payload }),
  updateStatus: (id: number, status: string) => request(`/orders/${id}/status`, { method: 'PATCH', body: { status } }),
  setDeliveryFee: (id: number, fee: number) => request(`/orders/${id}/delivery-fee`, { method: 'PATCH', body: { delivery_fee: fee } }),
  updatePaymentStatus: (id: number, payment_status: string) =>
    request(`/orders/${id}/payment-status`, { method: 'PATCH', body: { payment_status } }),
  paymentProof: (id: number) => requestBlob(`/orders/${id}/payment-proof`),
};

export const reportApi = {
  finance: (period = 'all', params?: Record<string, string>) => request('/reports/finance', { params: { period, ...params } }),
  analytics: (period = 'all') => request('/reports/analytics', { params: { period } }),
};

export const subscriptionApi = {
  get: () => request('/subscription'),
  plans: () => request('/plans'),
  startTrial: (planId: number) => request('/subscription/trial', { method: 'POST', body: { plan_id: planId } }),
  portal: () => request('/subscription/portal', { method: 'POST' }),
};

const PLATFORM_BASE = (import.meta as any).env?.VITE_API_URL || '/api/v1';

export const platformApi = {
  overview: () => request('/platform/overview', { base: PLATFORM_BASE }),
  analytics: () => request('/platform/analytics', { base: PLATFORM_BASE }),
  auditLogs: (params?: Record<string, string>) => request('/platform/audit-logs', { base: PLATFORM_BASE, params }),
  planFeatures: () => request('/platform/plan-features', { base: PLATFORM_BASE }),
  deliveryProviders: {
    list: () => request('/platform/delivery-providers', { base: PLATFORM_BASE }),
    create: (payload: Record<string, unknown>) => request('/platform/delivery-providers', { base: PLATFORM_BASE, method: 'POST', body: payload }),
    update: (id: number, payload: Record<string, unknown>) => request(`/platform/delivery-providers/${id}`, { base: PLATFORM_BASE, method: 'PUT', body: payload }),
    destroy: (id: number) => request(`/platform/delivery-providers/${id}`, { base: PLATFORM_BASE, method: 'DELETE' }),
  },
  plans: {
    list: () => request('/platform/plans', { base: PLATFORM_BASE }),
    create: (payload: Record<string, unknown>) => request('/platform/plans', { base: PLATFORM_BASE, method: 'POST', body: payload }),
    update: (id: number, payload: Record<string, unknown>) => request(`/platform/plans/${id}`, { base: PLATFORM_BASE, method: 'PUT', body: payload }),
    destroy: (id: number) => request(`/platform/plans/${id}`, { base: PLATFORM_BASE, method: 'DELETE' }),
  },
  stores: {
    list: (params?: Record<string, string>) => request('/platform/stores', { base: PLATFORM_BASE, params }),
    show: (id: number) => request(`/platform/stores/${id}`, { base: PLATFORM_BASE }),
    analytics: (id: number) => request(`/platform/stores/${id}/analytics`, { base: PLATFORM_BASE }),
    suspend: (id: number) => request(`/platform/stores/${id}/suspend`, { base: PLATFORM_BASE, method: 'PUT' }),
    reactivate: (id: number) => request(`/platform/stores/${id}/reactivate`, { base: PLATFORM_BASE, method: 'PUT' }),
    assignPlan: (id: number, planId: number) => request(`/platform/stores/${id}/plan`, { base: PLATFORM_BASE, method: 'PUT', body: { plan_id: planId } }),
    impersonate: (id: number) => request(`/platform/stores/${id}/impersonate`, { base: PLATFORM_BASE, method: 'POST' }),
    destroy: (id: number, confirmName: string) => request(`/platform/stores/${id}`, { base: PLATFORM_BASE, method: 'DELETE', body: { confirm_name: confirmName } }),
  },
  customers: (params?: Record<string, string>) => request('/platform/customers', { base: PLATFORM_BASE, params }),
  products: (params?: Record<string, string>) => request('/platform/products', { base: PLATFORM_BASE, params }),
  exportCsv: (kind: 'customers' | 'products' | 'stores' | 'orders') =>
    downloadFile(`/platform/${kind}/export`, `${kind}-${new Date().toISOString().slice(0, 10)}.csv`, PLATFORM_BASE),
  importCustomers: (storeId: number, file: File) => {
    const fd = new FormData();
    fd.append('store_id', String(storeId));
    fd.append('file', file);
    return request('/platform/customers/import', { base: PLATFORM_BASE, method: 'POST', body: fd });
  },
  users: {
    list: (params?: Record<string, string>) => request('/platform/users', { base: PLATFORM_BASE, params }),
    grantAdmin: (id: number) => request(`/platform/users/${id}/grant-admin`, { base: PLATFORM_BASE, method: 'PUT' }),
    revokeAdmin: (id: number) => request(`/platform/users/${id}/revoke-admin`, { base: PLATFORM_BASE, method: 'PUT' }),
    setActive: (id: number, isActive: boolean) => request(`/platform/users/${id}/active`, { base: PLATFORM_BASE, method: 'PUT', body: { is_active: isActive } }),
    resetPassword: (id: number) => request(`/platform/users/${id}/reset-password`, { base: PLATFORM_BASE, method: 'POST' }),
  },
};

// Anonymous storefront (no credentials needed; different base).
export const publicApi = {
  store: (slug: string) => request(`/stores/${slug}`, { base: PUBLIC_BASE, auth: false }),
  deliveryQuote: (slug: string, params: { lat: string; lng: string; subtotal: string; delivery_provider_id?: string }) =>
    request(`/stores/${slug}/delivery-quote`, { base: PUBLIC_BASE, params, auth: false }),
  catalog: (slug: string, params?: Record<string, string>) => request(`/stores/${slug}/catalog`, { base: PUBLIC_BASE, params, auth: false }),
  createOrder: (slug: string, payload: Record<string, unknown> | FormData) =>
    request(`/stores/${slug}/orders`, { base: PUBLIC_BASE, method: 'POST', body: payload, auth: false }),
  getOrder: (slug: string, orderNumber: string) =>
    request(`/stores/${slug}/orders/${orderNumber}`, { base: PUBLIC_BASE, auth: false }),
  uploadOrderPaymentProof: (slug: string, orderNumber: string, file: File) => {
    const fd = new FormData();
    fd.append('payment_proof', file);
    return request(`/stores/${slug}/orders/${orderNumber}/payment-proof`, { base: PUBLIC_BASE, method: 'POST', body: fd, auth: false });
  },
  lookupCustomer: (slug: string, phone: string) =>
    request(`/stores/${slug}/customers/lookup`, { base: PUBLIC_BASE, params: { phone }, auth: false }),
};
