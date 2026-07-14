import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { TextInput } from '../../components/forms/TextInput';
import { AppButton } from '../../components/buttons/AppButton';

export const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const { login, error } = useAuthStore();
  const [email, setEmail] = useState('merchant@dorzak.com');
  const [password, setPassword] = useState('password');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await login(email, password);
      const { store, user } = useAuthStore.getState();
      navigate(store === null && user?.is_platform_admin ? '/platform' : '/checkout');
    } catch {
      /* error surfaced from store */
    } finally {
      setLoading(false);
    }
  };

  return (
    <div
      style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: 'var(--color-bg)',
      }}
    >
      <form
        onSubmit={handleSubmit}
        className="card"
        style={{
          width: '380px',
          display: 'flex',
          flexDirection: 'column',
          gap: '18px',
          padding: '32px',
        }}
      >
        <div style={{ textAlign: 'center' }}>
          <div
            style={{
              width: '48px',
              height: '48px',
              borderRadius: '12px',
              backgroundColor: 'var(--dorzak-primary)',
              color: '#fff',
              display: 'inline-flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontWeight: 800,
              fontSize: '1.4rem',
            }}
          >
            D
          </div>
          <h2 style={{ margin: '12px 0 2px 0' }}>Dorzak Merchant</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
            Sign in to your store
          </span>
        </div>

        {error && (
          <div
            style={{
              backgroundColor: 'var(--dorzak-danger-light, #fee2e2)',
              color: 'var(--dorzak-danger, #b91c1c)',
              padding: '10px 12px',
              borderRadius: '8px',
              fontSize: '0.85rem',
            }}
          >
            {error}
          </div>
        )}

        <TextInput
          label="Email"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="you@store.com"
        />
        <TextInput
          label="Password"
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          placeholder="••••••••"
        />

        <AppButton
          variant="primary"
          type="submit"
          loading={loading}
          style={{ width: '100%', padding: '12px' }}
        >
          Sign In
        </AppButton>

        <span style={{ textAlign: 'center', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
          New here?{' '}
          <Link to="/signup" style={{ color: 'var(--dorzak-primary)', fontWeight: 600 }}>
            Create your store — free
          </Link>
        </span>

        <span style={{ textAlign: 'center', fontSize: '0.75rem', color: 'var(--text-muted)' }}>
          Demo: merchant@dorzak.com / password
        </span>
      </form>
    </div>
  );
};
