import { act, render } from '@testing-library/react';
import { beforeEach, expect, test, vi } from 'vitest';
import { useOrderStore } from '../stores/orderStore';
import { useOrderPolling } from './useOrderPolling';

const doubles = vi.hoisted(() => ({
  addToast: vi.fn(),
  listOrders: vi.fn(),
  playSound: vi.fn(),
  requestPermission: vi.fn(),
  showNotification: vi.fn(),
}));

vi.mock('../api/endpoints', () => ({
  orderApi: { list: doubles.listOrders },
}));

vi.mock('../stores/toastStore', () => ({
  useToastStore: () => ({ addToast: doubles.addToast }),
}));

vi.mock('../utils/notificationSound', () => ({
  playOrderNotificationSound: doubles.playSound,
  requestNotificationPermission: doubles.requestPermission,
  showOrderNotification: doubles.showNotification,
}));

const PollingHarness = () => {
  useOrderPolling(true);
  return null;
};

function apiOrder(id: string, customerName: string) {
  return {
    id,
    order_number: id,
    customer_name: customerName,
    items: [],
    payment_method: 'CASH',
    status: 'ACCEPTED',
  };
}

async function fetchOrders(orders: Array<ReturnType<typeof apiOrder>>) {
  doubles.listOrders.mockResolvedValueOnce({ data: orders });
  await act(async () => {
    await useOrderStore.getState().fetchOrders();
  });
}

beforeEach(() => {
  vi.clearAllMocks();
  doubles.requestPermission.mockResolvedValue(null);
  useOrderStore.setState({
    error: null,
    hasFetchedOrders: false,
    loading: false,
    orders: [],
    unseenOrderIds: [],
  });
});

test('baselines each merchant only after hydration and announces later new orders', async () => {
  render(<PollingHarness />);

  doubles.listOrders.mockRejectedValueOnce(new Error('Merchant A hydration failed'));
  await act(async () => {
    await useOrderStore.getState().fetchOrders();
  });
  expect.soft(useOrderStore.getState()).toMatchObject({
    hasFetchedOrders: false,
    orders: [],
  });
  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.playSound).not.toHaveBeenCalled();
  expect.soft(doubles.showNotification).not.toHaveBeenCalled();

  const merchantAExisting = apiOrder('A-EXISTING', 'Alice Existing');
  await fetchOrders([merchantAExisting]);
  expect.soft(useOrderStore.getState()).toMatchObject({ hasFetchedOrders: true });
  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.playSound).not.toHaveBeenCalled();
  expect.soft(doubles.showNotification).not.toHaveBeenCalled();
  expect.soft(useOrderStore.getState().unseenOrderIds).toEqual([]);

  doubles.addToast.mockClear();
  doubles.playSound.mockClear();
  doubles.showNotification.mockClear();
  act(() => useOrderStore.setState({ unseenOrderIds: [] }));
  const merchantANew = apiOrder('A-NEW', 'Alice New');
  await fetchOrders([merchantANew, merchantAExisting]);
  expect(useOrderStore.getState().unseenOrderIds).toEqual(['A-NEW']);
  expect(doubles.addToast).toHaveBeenCalledWith('New order A-NEW from Alice New', 'info');
  expect(doubles.playSound).toHaveBeenCalledTimes(1);
  expect(doubles.showNotification).toHaveBeenCalledWith('New order received', 'A-NEW — Alice New');

  await act(async () => {
    useOrderStore.setState({
      error: null,
      hasFetchedOrders: false,
      loading: false,
      orders: [],
      unseenOrderIds: [],
    });
  });
  expect.soft(useOrderStore.getState()).toMatchObject({
    hasFetchedOrders: false,
    orders: [],
    unseenOrderIds: [],
  });
  doubles.addToast.mockClear();
  doubles.playSound.mockClear();
  doubles.showNotification.mockClear();

  const merchantBExisting = apiOrder('B-EXISTING', 'Bob Existing');
  await fetchOrders([merchantBExisting]);
  expect.soft(useOrderStore.getState()).toMatchObject({ hasFetchedOrders: true });
  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.playSound).not.toHaveBeenCalled();
  expect.soft(doubles.showNotification).not.toHaveBeenCalled();
  expect.soft(useOrderStore.getState().unseenOrderIds).toEqual([]);

  doubles.addToast.mockClear();
  doubles.playSound.mockClear();
  doubles.showNotification.mockClear();
  act(() => useOrderStore.setState({ unseenOrderIds: [] }));
  const merchantBNew = apiOrder('B-NEW', 'Bob New');
  await fetchOrders([merchantBNew, merchantBExisting]);
  expect(useOrderStore.getState().unseenOrderIds).toEqual(['B-NEW']);
  expect(doubles.addToast).toHaveBeenCalledWith('New order B-NEW from Bob New', 'info');
  expect(doubles.playSound).toHaveBeenCalledTimes(1);
  expect(doubles.showNotification).toHaveBeenCalledWith('New order received', 'B-NEW — Bob New');
});
