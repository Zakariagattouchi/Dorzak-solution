import { act, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, expect, test, vi } from 'vitest';
import { invalidateMerchantScope } from '../../stores/merchantScope';
import { CustomerModal } from './CustomerModal';
import { PaymentModal } from './PaymentModal';
import { ProductModal } from './ProductModal';

function deferred<T>() {
  let resolve!: (value: T | PromiseLike<T>) => void;
  let reject!: (reason?: unknown) => void;
  const promise = new Promise<T>((resolvePromise, rejectPromise) => {
    resolve = resolvePromise;
    reject = rejectPromise;
  });
  return { promise, resolve, reject };
}

const doubles = vi.hoisted(() => ({
  activeModal: null as string | null,
  addCustomer: vi.fn(),
  addProduct: vi.fn(),
  addToast: vi.fn(),
  clearCart: vi.fn(),
  closeModal: vi.fn(),
  createOrder: vi.fn(),
  fetchProducts: vi.fn(),
  openModal: vi.fn(),
}));

vi.mock('../../stores/modalStore', () => ({
  useModalStore: () => ({
    activeModal: doubles.activeModal,
    closeModal: doubles.closeModal,
    openModal: doubles.openModal,
    payload: null,
  }),
}));

vi.mock('../../stores/toastStore', () => ({
  useToastStore: () => ({ addToast: doubles.addToast }),
}));

vi.mock('../../stores/customerStore', () => ({
  useCustomerStore: () => ({ addCustomer: doubles.addCustomer }),
}));

vi.mock('../../stores/productStore', () => {
  const useProductStore = () => ({
    addProduct: doubles.addProduct,
    categories: [{ id: 'category-1', name: 'General', productCount: 0 }],
  });
  useProductStore.getState = () => ({ fetchProducts: doubles.fetchProducts });
  return { useProductStore };
});

vi.mock('../../stores/orderStore', () => ({
  useOrderStore: () => ({ createOrder: doubles.createOrder }),
}));

vi.mock('../../stores/cartStore', () => ({
  useCartStore: () => ({
    clearCart: doubles.clearCart,
    discount: 0,
    getSubtotal: () => 20,
    getTotal: () => 20,
    items: [
      {
        lineKey: 'product-1:base',
        product: { id: 'product-1', name: 'Test Product', price: 20 },
        quantity: 1,
      },
    ],
    selectedCustomer: null,
  }),
}));

vi.mock('../../stores/settingsStore', () => ({
  useSettingsStore: (
    selector: (state: {
      accountInfo: {
        chargeSalesTax: boolean;
        currency: string;
        taxIncludedInPrice: boolean;
        taxRate: number;
      };
    }) => unknown,
  ) =>
    selector({
      accountInfo: {
        chargeSalesTax: false,
        currency: 'QAR',
        taxIncludedInPrice: false,
        taxRate: 0,
      },
    }),
}));

vi.mock('../../hooks/useMoney', () => ({
  useMoney: () => (amount: number) => `QAR ${amount.toFixed(2)}`,
}));

vi.mock('../forms/LocationPicker', () => ({ LocationPicker: () => null }));

beforeEach(() => {
  vi.clearAllMocks();
  doubles.activeModal = null;
  doubles.addCustomer.mockResolvedValue(undefined);
  doubles.addProduct.mockResolvedValue(undefined);
  doubles.createOrder.mockResolvedValue({ id: 'ORDER-1', total: 20 });
  doubles.fetchProducts.mockResolvedValue(undefined);
});

test('product modal preserves its form and suppresses late completion after replacement', async () => {
  const user = userEvent.setup();
  const save = deferred<unknown>();
  doubles.activeModal = 'PRODUCT_CREATE';
  doubles.addProduct.mockImplementationOnce(() => save.promise);

  render(<ProductModal />);
  await user.type(screen.getByLabelText('Product Title *'), 'Old merchant product');
  await user.click(screen.getByRole('button', { name: 'Pricing & Profit' }));
  await user.type(screen.getByLabelText(/Selling Price/), '15');
  const saveButton = screen.getByRole('button', { name: 'Save Product' });
  await user.click(saveButton);
  await waitFor(() => expect(doubles.addProduct).toHaveBeenCalledTimes(1));

  invalidateMerchantScope();
  await act(async () => {
    save.resolve(undefined);
    await save.promise;
  });

  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.closeModal).not.toHaveBeenCalled();
  expect.soft(saveButton).toBeDisabled();
  await user.click(screen.getByRole('button', { name: 'Basic Info' }));
  expect.soft(screen.getByLabelText('Product Title *')).toHaveValue('Old merchant product');
});

test('customer modal preserves its form and suppresses late completion after replacement', async () => {
  const user = userEvent.setup();
  const save = deferred<unknown>();
  doubles.activeModal = 'CUSTOMER_CREATE';
  doubles.addCustomer.mockImplementationOnce(() => save.promise);

  render(<CustomerModal />);
  await user.type(screen.getByLabelText('Full Name *'), 'Old Merchant Customer');
  await user.type(screen.getByLabelText('Phone Number *'), '+97455550000');
  const saveButton = screen.getByRole('button', { name: 'Save Customer' });
  await user.click(saveButton);
  await waitFor(() => expect(doubles.addCustomer).toHaveBeenCalledTimes(1));

  invalidateMerchantScope();
  await act(async () => {
    save.resolve(undefined);
    await save.promise;
  });

  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.closeModal).not.toHaveBeenCalled();
  expect.soft(saveButton).toBeDisabled();
  expect.soft(screen.getByLabelText('Full Name *')).toHaveValue('Old Merchant Customer');
  expect.soft(screen.getByLabelText('Phone Number *')).toHaveValue('+97455550000');
});

test('payment modal suppresses late sale UI, cart, and catalog continuations after replacement', async () => {
  const user = userEvent.setup();
  const sale = deferred<{ id: string; total: number }>();
  doubles.activeModal = 'PAYMENT';
  doubles.createOrder.mockImplementationOnce(() => sale.promise);

  render(<PaymentModal />);
  const completeButton = screen.getByRole('button', { name: /Complete Sale/ });
  await user.click(completeButton);
  await waitFor(() => expect(doubles.createOrder).toHaveBeenCalledTimes(1));

  invalidateMerchantScope();
  await act(async () => {
    sale.resolve({ id: 'ORDER-OLD', total: 20 });
    await sale.promise;
  });

  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.clearCart).not.toHaveBeenCalled();
  expect.soft(doubles.openModal).not.toHaveBeenCalled();
  expect.soft(doubles.fetchProducts).not.toHaveBeenCalled();
  expect.soft(completeButton).toBeDisabled();
});
