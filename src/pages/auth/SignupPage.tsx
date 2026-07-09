import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { TextInput } from '../../components/forms/TextInput';
import { AppButton } from '../../components/buttons/AppButton';

export const SignupPage: React.FC = () => {
  const navigate = useNavigate();
  const { register, error } = useAuthStore();
  const [businessName, setBusinessName] = useState('');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await register({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        business_name: businessName,
      });
      navigate('/checkout');
    } catch {
      /* error surfaced from store */
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', backgroundColor: 'var(--color-bg)' }}>
      <form onSubmit={handleSubmit} className="card" style={{ width: '400px', display: 'flex', flexDirection: 'column', gap: '16px', padding: '32px' }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{ width: '48px', height: '48px', borderRadius: '12px', backgroundColor: 'var(--dorzak-primary)', color: '#fff', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontWeight: 800, fontSize: '1.4rem' }}>D</div>
          <h2 style={{ margin: '12px 0 2px 0' }}>Create your store</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
            Free forever plan — no credit card needed
          </span>
        </div>

        {error && (
          <div style={{ backgroundColor: 'var(--dorzak-danger-light, #fee2e2)', color: 'var(--dorzak-danger, #b91c1c)', padding: '10px 12px', borderRadius: '8px', fontSize: '0.85rem' }}>
            {error}
          </div>
        )}

        <TextInput label="Business name" value={businessName} onChange={(e) => setBusinessName(e.target.value)} placeholder="Fresh Bites Café" required />
        <TextInput label="Your name" value={name} onChange={(e) => setName(e.target.value)} placeholder="Jane Merchant" required />
        <TextInput label="Email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="you@store.com" required />
        <TextInput label="Password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder="At least 8 characters" required />
        <TextInput label="Confirm password" type="password" value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)} placeholder="Repeat your password" required />

        <AppButton variant="primary" type="submit" loading={loading} style={{ width: '100%', padding: '12px' }}>
          Create My Store
        </AppButton>

        <span style={{ textAlign: 'center', fontSize: '0.8rem', color: 'var(--text-muted)' }}>
          Already have a store? <Link to="/login" style={{ color: 'var(--dorzak-primary)', fontWeight: 600 }}>Sign in</Link>
        </span>
      </form>
    </div>
  );
};
