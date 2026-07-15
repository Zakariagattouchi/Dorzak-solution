import { create } from 'zustand';
import { Customer } from '../data/mockData';
import { customerApi } from '../api/endpoints';
import { toCustomer, customerPayload } from '../api/adapters';
import {
  captureMerchantScope,
  isMerchantScopeCurrent,
  requireCurrentMerchantScope,
} from './merchantScope';

interface CustomerState {
  customers: Customer[];
  loading: boolean;
  error: string | null;
  fetchCustomers: () => Promise<void>;
  addCustomer: (customer: Omit<Customer, 'id' | 'totalOrders' | 'totalSpent'>) => Promise<Customer>;
  updateCustomer: (id: string, updates: Partial<Customer>) => Promise<void>;
  deleteCustomer: (id: string) => Promise<void>;
}

export const useCustomerStore = create<CustomerState>((set) => ({
  customers: [],
  loading: false,
  error: null,

  fetchCustomers: async () => {
    const scope = captureMerchantScope();
    set({ loading: true, error: null });
    try {
      const res: any = await customerApi.list({ per_page: 200 });
      requireCurrentMerchantScope(scope);
      set({ customers: res.data.map(toCustomer), loading: false });
    } catch (e: any) {
      if (!isMerchantScopeCurrent(scope)) return;
      set({ loading: false, error: e.message ?? 'Failed to load customers' });
    }
  },

  addCustomer: async (customerData) => {
    const scope = captureMerchantScope();
    const res: any = await customerApi.create(customerPayload(customerData));
    requireCurrentMerchantScope(scope);
    const created = toCustomer(res.data);
    set((s) => ({ customers: [created, ...s.customers] }));
    return created;
  },

  updateCustomer: async (id, updates) => {
    const scope = captureMerchantScope();
    const res: any = await customerApi.update(Number(id), customerPayload(updates));
    requireCurrentMerchantScope(scope);
    const updated = toCustomer(res.data);
    set((s) => ({ customers: s.customers.map((c) => (c.id === id ? updated : c)) }));
  },

  deleteCustomer: async (id) => {
    const scope = captureMerchantScope();
    await customerApi.remove(Number(id));
    requireCurrentMerchantScope(scope);
    set((s) => ({ customers: s.customers.filter((c) => c.id !== id) }));
  },
}));
