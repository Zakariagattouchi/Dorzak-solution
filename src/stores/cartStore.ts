import { create } from 'zustand';
import { Product, ProductVariant, Customer } from '../data/mockData';

export interface CartItem {
  product: Product;
  variant?: ProductVariant;
  lineKey: string;
  quantity: number;
}

interface CartState {
  items: CartItem[];
  selectedCustomer: Customer | null;
  discount: number;
  addItem: (product: Product, variant?: ProductVariant) => void;
  removeItem: (lineKey: string) => void;
  updateQuantity: (lineKey: string, quantity: number) => void;
  setSelectedCustomer: (customer: Customer | null) => void;
  setDiscount: (discount: number) => void;
  clearCart: () => void;
  getSubtotal: () => number;
  getTotal: () => number;
}

export const useCartStore = create<CartState>((set, get) => ({
  items: [],
  selectedCustomer: null,
  discount: 0,
  addItem: (product, variant) => {
    const { items } = get();
    const lineKey = `${product.id}:${variant?.id ?? 'base'}`;
    const existing = items.find((item) => item.lineKey === lineKey);
    if (existing) {
      set({
        items: items.map((item) =>
          item.lineKey === lineKey ? { ...item, quantity: item.quantity + 1 } : item,
        ),
      });
    } else {
      set({ items: [...items, { product, variant, lineKey, quantity: 1 }] });
    }
  },
  removeItem: (lineKey) => {
    set((state) => ({
      items: state.items.filter((item) => item.lineKey !== lineKey),
    }));
  },
  updateQuantity: (lineKey, quantity) => {
    if (quantity <= 0) {
      get().removeItem(lineKey);
      return;
    }
    set((state) => ({
      items: state.items.map((item) => (item.lineKey === lineKey ? { ...item, quantity } : item)),
    }));
  },
  setSelectedCustomer: (selectedCustomer) => set({ selectedCustomer }),
  setDiscount: (discount) => set({ discount }),
  clearCart: () => set({ items: [], selectedCustomer: null, discount: 0 }),
  getSubtotal: () => {
    return get().items.reduce(
      (sum, item) => sum + (item.variant?.price ?? item.product.price) * item.quantity,
      0,
    );
  },
  getTotal: () => {
    const subtotal = get().getSubtotal();
    return Math.max(0, subtotal - get().discount);
  },
}));
