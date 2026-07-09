const KEY = 'dorzak-pending-order';

export interface PendingOrder {
  orderNumber: string;
  slug: string;
}

export const savePendingOrder = (order: PendingOrder): void => {
  try { localStorage.setItem(KEY, JSON.stringify(order)); } catch {}
};

export const getPendingOrder = (): PendingOrder | null => {
  try {
    const raw = localStorage.getItem(KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    if (parsed?.orderNumber && parsed?.slug) return parsed;
    return null;
  } catch { return null; }
};

export const clearPendingOrder = (): void => {
  try { localStorage.removeItem(KEY); } catch {}
};
