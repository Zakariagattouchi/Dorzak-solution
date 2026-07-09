import React from 'react';
import { useToastStore } from '../../stores/toastStore';
import { AppIcon } from '../../components/icons/AppIcon';
import { AppButton } from '../../components/buttons/AppButton';
import { StatusPill } from '../../components/feedback/StatusPill';

export const BillingPage: React.FC = () => {
  const { addToast } = useToastStore();

  return (
    <div style={{ maxWidth: '900px', display: 'flex', flexDirection: 'column', gap: '24px' }}>
      <div>
        <h2 style={{ margin: 0 }}>Plans & Subscription</h2>
        <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Manage your Dorzak Merchant plan and feature modules</span>
      </div>

      <div className="card" style={{ borderLeft: '4px solid var(--dorzak-primary)' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '4px' }}>
              <h3 style={{ margin: 0, fontSize: '1.2rem' }}>Dorzak Merchant Pro</h3>
              <StatusPill status="ACTIVE" label="PRO ACTIVE" />
            </div>
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Renews automatically on Jan 1, 2027</span>
          </div>
          <AppButton variant="secondary" onClick={() => addToast('Manage subscription portal opened', 'info')}>
            Manage Billing
          </AppButton>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
        <div className="card">
          <h4 style={{ margin: '0 0 12px 0', fontSize: '1rem' }}>Included PRO Features</h4>
          <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: '10px', fontSize: '0.9rem' }}>
            <li style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <AppIcon name="checkCircle" size={16} color="var(--dorzak-success)" />
              Unlimited POS Checkout & Orders
            </li>
            <li style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <AppIcon name="checkCircle" size={16} color="var(--dorzak-success)" />
              Unlimited Catalog & Stock Sync
            </li>
            <li style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <AppIcon name="checkCircle" size={16} color="var(--dorzak-success)" />
              Customer CRM & Spend Tracking
            </li>
            <li style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <AppIcon name="checkCircle" size={16} color="var(--dorzak-success)" />
              Public Online Storefront Link
            </li>
          </ul>
        </div>

        <div className="card" style={{ backgroundColor: 'var(--dorzak-primary-light)' }}>
          <h4 style={{ margin: '0 0 8px 0', fontSize: '1rem', color: 'var(--dorzak-primary)' }}>Need Enterprise Custom Features?</h4>
          <p style={{ fontSize: '0.85rem', color: 'var(--text-main)', marginBottom: '16px' }}>
            Upgrade to Enterprise for multi-store sync, custom API webhooks, and dedicated account manager support.
          </p>
          <AppButton variant="primary" onClick={() => addToast('Sales team contact modal triggered', 'info')}>
            Contact Sales
          </AppButton>
        </div>
      </div>
    </div>
  );
};
