import { useEffect, useRef } from 'react';
import { useOrderStore } from '../stores/orderStore';
import { useToastStore } from '../stores/toastStore';
import {
  playOrderNotificationSound,
  requestNotificationPermission,
  showOrderNotification,
} from '../utils/notificationSound';

const POLL_INTERVAL_MS = 10_000;

export const useOrderPolling = (enabled: boolean): void => {
  const { orders, hasFetchedOrders, fetchOrders, addUnseenOrder } = useOrderStore();
  const { addToast } = useToastStore();
  const previousIdsRef = useRef<Set<string>>(new Set());
  const initializedRef = useRef(false);

  useEffect(() => {
    if (!enabled) return;

    requestNotificationPermission().catch(() => {});

    if (!hasFetchedOrders) {
      previousIdsRef.current = new Set();
      initializedRef.current = false;
      return;
    }

    const poll = async () => {
      await fetchOrders();
    };

    const detectNewOrders = () => {
      const currentIds = new Set(orders.map((o) => o.id));

      if (!initializedRef.current) {
        previousIdsRef.current = currentIds;
        initializedRef.current = true;
        return;
      }

      const newOrders = orders.filter((o) => !previousIdsRef.current.has(o.id));
      if (newOrders.length > 0) {
        newOrders.forEach((o) => addUnseenOrder(o.id));
        playOrderNotificationSound();

        if (newOrders.length === 1) {
          const order = newOrders[0];
          addToast(`New order ${order.id} from ${order.customerName}`, 'info');
          showOrderNotification('New order received', `${order.id} — ${order.customerName}`);
        } else {
          addToast(`${newOrders.length} new orders received`, 'info');
          showOrderNotification(
            'New orders received',
            `${newOrders.length} new orders are waiting.`,
          );
        }
      }

      previousIdsRef.current = currentIds;
    };

    detectNewOrders();
  }, [orders, hasFetchedOrders, enabled, fetchOrders, addUnseenOrder, addToast]);

  useEffect(() => {
    if (!enabled) return;

    const tick = () => {
      fetchOrders().catch(() => {});
    };

    const id = setInterval(tick, POLL_INTERVAL_MS);
    return () => clearInterval(id);
  }, [enabled, fetchOrders]);
};
