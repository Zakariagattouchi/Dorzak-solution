import { create } from 'zustand';
import { Customer } from '../data/mockData';
import { customerApi } from '../api/endpoints';
import { toCustomer, customerPayload } from '../api/adapters';

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
    set({ loading: true, error: null });
    try {
      const res: any = await customerApi.list({ per_page: 200 });
      set({ customers: res.data.map(toCustomer), loading: false });
    } catch (e: any) {
      set({ loading: false, error: e.message ?? 'Failed to load customers' });
    }
  },

  addCustomer: async (customerData) => {
    const res: any = await customerApi.create(customerPayload(customerData));
    const created = toCustomer(res.data);
    set((s) => ({ customers: [created, ...s.customers] }));
    return created;
  },

  updateCustomer: async (id, updates) => {
    const res: any = await customerApi.update(Number(id), customerPayload(updates));
    const updated = toCustomer(res.data);
    set((s) => ({ customers: s.customers.map((c) => (c.id === id ? updated : c)) }));
  },

  deleteCustomer: async (id) => {
    await customerApi.remove(Number(id));
    set((s) => ({ customers: s.customers.filter((c) => c.id !== id) }));
  },
}));
