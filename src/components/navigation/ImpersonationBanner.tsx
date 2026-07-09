import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { AppIcon } from '../icons/AppIcon';

/**
 * Persistent warning bar shown while a platform admin is impersonating a store.
 * Always on top so the operator can never forget they're inside someone else's
 * account — and always one click from exiting back to the platform console.
 */
export const ImpersonationBanner: React.FC = () => {
  const navigate = useNavigate();
  const { impersonating, stopImpersonating } = useAuthStore();
  const [exiting, setExiting] = useState(false);

  if (!impersonating) return null;

  const handleExit = async () => {
    setExiting(true);
    try {
      await stopImpersonating();
      navigate('/platform');
    } finally {
      setExiting(false);
    }
  };

  return (
    <div
      role="alert"
      style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: '12px',
        padding: '8px 16px',
        backgroundColor: '#7c2d12',
        color: '#fff',
        fontSize: '0.85rem',
        fontWeight: 600,
        flexWrap: 'wrap',
      }}
    >
      <AppIcon name="eye" size={16} color="#fff" />
      <span>
        Viewing <strong>{impersonating}</strong> as its owner — actions you take affect their live store.
      </span>
      <button
        type="button"
        onClick={handleExit}
        disabled={exiting}
        style={{
          background: '#fff',
          color: '#7c2d12',
          border: 'none',
          borderRadius: '6px',
          padding: '4px 12px',
          fontSize: '0.8rem',
          fontWeight: 700,
          cursor: exiting ? 'wait' : 'pointer',
        }}
      >
        {exiting ? 'Exiting…' : 'Exit impersonation'}
      </button>
    </div>
  );
};
