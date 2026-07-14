import React from 'react';
import { AppIcon } from '../icons/AppIcon';
import { AppButton } from '../buttons/AppButton';
import { useSettingsStore } from '../../stores/settingsStore';
import { useNavigate } from 'react-router-dom';

interface TopbarProps {
  onOpenMenu?: () => void;
}

export const Topbar: React.FC<TopbarProps> = ({ onOpenMenu }) => {
  const { accountInfo } = useSettingsStore();
  const navigate = useNavigate();

  return (
    <header className="app-topbar">
      <div className="topbar-left">
        <AppButton
          variant="icon"
          className="mobile-menu-button"
          aria-label="Open navigation"
          onClick={onOpenMenu}
        >
          <AppIcon name="menu" size={22} />
        </AppButton>
        <div className="business-switcher">
          <div className="business-avatar">{accountInfo.businessName.substring(0, 1)}</div>
          <div className="business-meta">
            <h4 className="business-name">{accountInfo.businessName}</h4>
            <span className="business-context">
              All branches · {accountInfo.country} · {accountInfo.currency}
            </span>
          </div>
        </div>
      </div>

      <div className="topbar-right">
        <AppButton icon="pos" variant="primary" onClick={() => navigate('/checkout')}>
          Quick Sale
        </AppButton>
        <AppButton icon="plus" variant="tertiary" onClick={() => navigate('/products/create')}>
          Add Product
        </AppButton>
      </div>
    </header>
  );
};
