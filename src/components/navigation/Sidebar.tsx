import React from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { mainNavigation } from '../../config/navigation';
import { AppIcon } from '../icons/AppIcon';
import { useAuthStore } from '../../stores/authStore';
import { useOrderStore } from '../../stores/orderStore';

interface SidebarProps {
  isOpen?: boolean;
  onNavigate?: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ isOpen = false, onNavigate }) => {
  const navigate = useNavigate();
  const { can, user, role, logout } = useAuthStore();
  const { unseenOrderIds, markOrdersSeen } = useOrderStore();

  // Show only the items the current role is allowed to use.
  const items = mainNavigation.filter((item) => can(item.ability));
  const hasUnseenOrders = unseenOrderIds.length > 0;

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  const handleNavigate = () => {
    markOrdersSeen();
    onNavigate?.();
  };

  return (
    <aside className={`app-sidebar ${isOpen ? 'is-open' : ''}`} aria-label="Primary navigation">
      <div className="sidebar-header">
        <div className="sidebar-logo">
          <div className="sidebar-brand-mark" aria-hidden="true">
            D
          </div>
          <span>Dorzak Merchant</span>
        </div>
      </div>

      <nav className="sidebar-menu">
        {items.map((item, index) => (
          <React.Fragment key={item.path}>
            {(index === 0 || items[index - 1].group !== item.group) && (
              <div className="sidebar-group-label">{item.group}</div>
            )}
            <NavLink
              to={item.path}
              onClick={item.path === '/orders' ? handleNavigate : onNavigate}
              className={({ isActive }) => `sidebar-item ${isActive ? 'active' : ''}`}
            >
              <span style={{ position: 'relative', display: 'inline-flex' }}>
                <AppIcon name={item.icon} size={20} />
                {item.path === '/orders' && hasUnseenOrders && (
                  <span className="sidebar-pulse-dot" aria-hidden="true" />
                )}
              </span>
              <span style={{ flex: 1 }}>{item.label}</span>
              {item.badge && <span className="sidebar-badge">{item.badge}</span>}
            </NavLink>
          </React.Fragment>
        ))}
      </nav>

      <div
        className="sidebar-footer"
        style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}
      >
        {user && (
          <div style={{ fontSize: '0.78rem' }}>
            <div style={{ fontWeight: 600, color: 'var(--kyte-sidebar-active)' }}>{user.name}</div>
            <div style={{ color: 'var(--kyte-sidebar-text)', opacity: 0.8 }}>{role}</div>
          </div>
        )}
        <button
          type="button"
          onClick={handleLogout}
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            fontSize: '0.82rem',
            color: 'var(--kyte-sidebar-text)',
            background: 'none',
            border: 'none',
            cursor: 'pointer',
            padding: '4px 0',
          }}
        >
          <AppIcon name="close" size={16} /> Sign out
        </button>
      </div>
    </aside>
  );
};
