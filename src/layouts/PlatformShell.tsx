import React, { useEffect } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../stores/authStore';
import { ToastHost } from '../components/feedback/ToastHost';

/**
 * Standalone chrome for the platform (super) admin — a completely separate
 * experience from the merchant back office. No Sell / Products / Categories
 * menus; the operator only ever sees platform-wide controls. Rendered only for
 * store-less admin sessions (store === null && is_platform_admin).
 */
export const PlatformShell: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const navigate = useNavigate();
  const { status, user, store, bootstrap, logout } = useAuthStore();

  useEffect(() => {
    if (status === 'idle') bootstrap();
  }, [status, bootstrap]);

  if (status === 'idle' || status === 'loading') {
    return (
      <div
        style={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: 'var(--text-muted)',
        }}
      >
        Loading the platform console…
      </div>
    );
  }

  if (status === 'guest') return <Navigate to="/login" replace />;

  // A merchant (has a store) is not a platform operator — send them home.
  if (store !== null || !user?.is_platform_admin) return <Navigate to="/checkout" replace />;

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div
      style={{
        minHeight: '100vh',
        display: 'flex',
        flexDirection: 'column',
        background: 'var(--color-bg)',
      }}
    >
      <header
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          padding: '12px 24px',
          background: '#17201e',
          color: '#fff',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
          <div
            style={{
              width: '30px',
              height: '30px',
              borderRadius: '8px',
              background: 'var(--dorzak-primary)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontWeight: 800,
            }}
          >
            D
          </div>
          <div>
            <div style={{ fontWeight: 700, fontSize: '0.95rem', lineHeight: 1.1 }}>
              Dorzak Platform
            </div>
            <div style={{ fontSize: '0.72rem', opacity: 0.7 }}>Super admin console</div>
          </div>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
          <span style={{ fontSize: '0.82rem', opacity: 0.85 }}>
            {user?.name} · {user?.email}
          </span>
          <button
            onClick={handleLogout}
            style={{
              background: 'rgba(255,255,255,0.12)',
              color: '#fff',
              border: 'none',
              borderRadius: '6px',
              padding: '6px 12px',
              fontSize: '0.8rem',
              fontWeight: 600,
              cursor: 'pointer',
            }}
          >
            Sign out
          </button>
        </div>
      </header>

      <main
        style={{ flex: 1, padding: '24px', maxWidth: '1120px', width: '100%', margin: '0 auto' }}
      >
        {children}
      </main>
      <ToastHost />
    </div>
  );
};
