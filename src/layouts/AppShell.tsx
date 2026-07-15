import React, { lazy, Suspense, useEffect, useState } from 'react';
import { Outlet, Navigate } from 'react-router-dom';
import { Sidebar } from '../components/navigation/Sidebar';
import { Topbar } from '../components/navigation/Topbar';
import { ImpersonationBanner } from '../components/navigation/ImpersonationBanner';
import { ToastHost } from '../components/feedback/ToastHost';
import { useAuthStore } from '../stores/authStore';
import { useProductStore } from '../stores/productStore';
import { useCustomerStore } from '../stores/customerStore';
import { useOrderStore } from '../stores/orderStore';
import { useSettingsStore } from '../stores/settingsStore';
import { useOrderPolling } from '../hooks/useOrderPolling';

const ModalHost = lazy(() =>
  import('../components/modals/ModalHost').then((module) => ({ default: module.ModalHost })),
);

const MerchantOrderPolling: React.FC = () => {
  useOrderPolling(true);
  return null;
};

export const AppShell: React.FC = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const { status, bootstrap, store, user } = useAuthStore();
  const { fetchProducts, fetchCategories } = useProductStore();
  const { fetchCustomers } = useCustomerStore();
  const { fetchOrders } = useOrderStore();
  const { fetchSettings } = useSettingsStore();

  // A store-less platform admin belongs in the platform console, not here.
  const isPlatformOnly =
    status === 'authenticated' && store === null && user?.is_platform_admin === true;
  const isMerchantStorePending =
    status === 'authenticated' && store === null && user?.is_platform_admin !== true;

  // Establish the session on first mount.
  useEffect(() => {
    if (status === 'idle') bootstrap();
  }, [status, bootstrap]);

  // Once authenticated as a MERCHANT, hydrate the store data (settings first — money formatting depends on it).
  useEffect(() => {
    if (status !== 'authenticated' || isPlatformOnly || !store) {
      return;
    }

    let cancelled = false;
    const hydrate = async () => {
      await fetchSettings();
      if (cancelled) return;

      await Promise.all([fetchProducts(), fetchCategories(), fetchCustomers(), fetchOrders()]);
    };

    void hydrate();

    return () => {
      cancelled = true;
    };
  }, [
    status,
    isPlatformOnly,
    store?.id,
    fetchSettings,
    fetchProducts,
    fetchCategories,
    fetchCustomers,
    fetchOrders,
  ]);

  if (status === 'idle' || status === 'loading' || isMerchantStorePending) {
    return (
      <div
        role="status"
        aria-label="Loading your store…"
        aria-live="polite"
        style={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: 'var(--text-muted)',
        }}
      >
        Loading your store…
      </div>
    );
  }

  if (status === 'guest') {
    return <Navigate to="/login" replace />;
  }

  if (isPlatformOnly) {
    return <Navigate to="/platform" replace />;
  }

  return (
    <div className="app-shell">
      <Sidebar isOpen={isMenuOpen} onNavigate={() => setIsMenuOpen(false)} />
      {isMenuOpen && (
        <button
          className="sidebar-scrim"
          aria-label="Close navigation"
          onClick={() => setIsMenuOpen(false)}
        />
      )}
      <div className="app-main">
        <ImpersonationBanner />
        <Topbar onOpenMenu={() => setIsMenuOpen(true)} />
        <main className="app-content">
          <Outlet />
        </main>
      </div>
      <Suspense fallback={null}>
        <ModalHost />
      </Suspense>
      <ToastHost />
      <MerchantOrderPolling key={store?.id} />
    </div>
  );
};
