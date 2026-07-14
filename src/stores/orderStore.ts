import { create } from 'zustand';
import { Order, OrderStatus, PaymentStatus } from '../data/mockData';
import { orderApi } from '../api/endpoints';
import { toOrder } from '../api/adapters';

interface OrderState {
  orders: Order[];
  loading: boolean;
  error: string | null;
  unseenOrderIds: string[];
  fetchOrders: () => Promise<void>;
  createOrder: (
    orderData: Omit<Order, 'id' | 'date'>,
    customerId?: string | null,
  ) => Promise<Order>;
  updateStatus: (order: Order, status: OrderStatus) => Promise<Order>;
  updatePaymentStatus: (order: Order, status: PaymentStatus) => Promise<Order>;
  markOrderSeen: (id: string) => void;
  markOrdersSeen: () => void;
  addUnseenOrder: (id: string) => void;
}

export const useOrderStore = create<OrderState>((set) => ({
  orders: [],
  loading: false,
  error: null,
  unseenOrderIds: [],

  fetchOrders: async () => {
    set({ loading: true, error: null });
    try {
      const res: any = await orderApi.list({ per_page: 100 });
      set({ orders: res.data.map(toOrder), loading: false });
    } catch (e: any) {
      set({ loading: false, error: e.message ?? 'Failed to load orders' });
    }
  },

  createOrder: async (orderData, customerId) => {
    const payload = {
      items: orderData.items.map((i) => ({
        product_id: Number(i.productId),
        variant_id: i.variantId ? Number(i.variantId) : null,
        quantity: i.quantity,
      })),
      customer_id: customerId ? Number(customerId) : null,
      discount: orderData.discount ?? 0,
      payment_method: orderData.paymentMethod,
      status: orderData.status ?? 'COMPLETE',
      notes: orderData.notes ?? null,
      source: 'pos',
    };
    const res: any = await orderApi.create(payload);
    const created = toOrder(res.data);
    set((s) => ({ orders: [created, ...s.orders] }));
    return created;
  },

  updateStatus: async (order, status) => {
    if (!order.apiId) throw new Error('Order identifier is missing.');
    const res: any = await orderApi.updateStatus(Number(order.apiId), status);
    const updated = toOrder(res.data);
    set((s) => ({ orders: s.orders.map((item) => (item.id === order.id ? updated : item)) }));
    return updated;
  },

  updatePaymentStatus: async (order, status) => {
    if (!order.apiId) throw new Error('Order identifier is missing.');
    const res: any = await orderApi.updatePaymentStatus(Number(order.apiId), status);
    const updated = toOrder(res.data);
    set((s) => ({ orders: s.orders.map((item) => (item.id === order.id ? updated : item)) }));
    return updated;
  },

  markOrderSeen: (id) =>
    set((s) => ({ unseenOrderIds: s.unseenOrderIds.filter((oid) => oid !== id) })),
  markOrdersSeen: () => set({ unseenOrderIds: [] }),
  addUnseenOrder: (id) =>
    set((s) => ({
      unseenOrderIds: s.unseenOrderIds.includes(id) ? s.unseenOrderIds : [...s.unseenOrderIds, id],
    })),
}));
