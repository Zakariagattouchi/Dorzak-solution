import React from 'react';
import { useToastStore } from '../../stores/toastStore';
import { AppIcon } from '../icons/AppIcon';

export const ToastHost: React.FC = () => {
  const { toasts, removeToast } = useToastStore();

  if (toasts.length === 0) return null;

  return (
    <div className="toast-host">
      {toasts.map((t) => (
        <div key={t.id} className={`toast ${t.type}`}>
          {t.type === 'success' && (
            <AppIcon name="checkCircle" size={18} color="var(--dorzak-success)" />
          )}
          {t.type === 'warning' && <AppIcon name="alert" size={18} color="var(--dorzak-warning)" />}
          {t.type === 'danger' && <AppIcon name="alert" size={18} color="var(--dorzak-danger)" />}
          {t.type === 'info' && <AppIcon name="info" size={18} color="var(--dorzak-primary)" />}
          <span>{t.message}</span>
          <button
            onClick={() => removeToast(t.id)}
            style={{ marginLeft: 'auto', color: 'var(--text-light)', padding: '2px' }}
          >
            <AppIcon name="close" size={14} />
          </button>
        </div>
      ))}
    </div>
  );
};
