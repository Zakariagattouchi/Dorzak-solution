import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon } from '../../components/icons/AppIcon';

/** One number the merchant cares about, in the app's stat-card style. */
export const StatCard: React.FC<{ label: string; value: React.ReactNode; hint?: string; tone?: 'success' | 'primary' | 'danger' | 'muted' }> = ({ label, value, hint, tone = 'primary' }) => {
  const color = tone === 'success' ? 'var(--dorzak-success)' : tone === 'danger' ? 'var(--dorzak-danger)' : tone === 'muted' ? 'var(--text-muted)' : 'var(--dorzak-primary)';
  return (
    <div className="card" style={{ padding: '14px' }}>
      <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.03em' }}>{label}</div>
      <div style={{ fontSize: '1.4rem', fontWeight: 800, color }}>{value}</div>
      {hint && <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', marginTop: 2 }}>{hint}</div>}
    </div>
  );
};

/** Section intro: what this feature does for the merchant, in one line. */
export const TabIntro: React.FC<{ title: string; sub: string; children?: React.ReactNode }> = ({ title, sub, children }) => (
  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12, flexWrap: 'wrap' }}>
    <div>
      <h3 style={{ margin: 0, fontSize: '1.05rem' }}>{title}</h3>
      <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>{sub}</span>
    </div>
    {children}
  </div>
);

/** Two-step inline destructive action — no accidental deletes, no modal needed. */
export const ConfirmButton: React.FC<{ label?: string; confirmLabel?: string; onConfirm: () => void }> = ({ label = 'Delete', confirmLabel = 'Confirm?', onConfirm }) => {
  const [arming, setArming] = useState(false);
  if (!arming) {
    return <AppButton variant="tertiary" style={{ color: 'var(--dorzak-danger)' }} onClick={() => { setArming(true); setTimeout(() => setArming(false), 3000); }}>{label}</AppButton>;
  }
  return <AppButton variant="danger" onClick={onConfirm}>{confirmLabel}</AppButton>;
};

/** Upgrade panel shown when the store's plan doesn't include a feature. */
export const LockedFeature: React.FC<{ title: string; description: string }> = ({ title, description }) => {
  const navigate = useNavigate();
  return (
    <div className="card" style={{ padding: '40px', textAlign: 'center', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 12 }}>
      <div style={{ width: 48, height: 48, borderRadius: '50%', background: 'var(--color-surface-alt, #f4f1ec)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <AppIcon name="sparkles" size={22} />
      </div>
      <h3 style={{ margin: 0 }}>{title} is not on your plan</h3>
      <p style={{ margin: 0, maxWidth: 420, color: 'var(--text-muted)', fontSize: '0.9rem' }}>{description}</p>
      <AppButton variant="primary" onClick={() => navigate('/billing')}>View plans</AppButton>
    </div>
  );
};

/** Uniform API-error toast copy. */
export const apiErrorMessage = (e: unknown): string => {
  const err = e as { status?: number; data?: { message?: string } };
  if (err?.status === 402) return 'This feature is not included in your plan. Upgrade to unlock it.';
  return err?.data?.message || 'Something went wrong. Please try again.';
};
