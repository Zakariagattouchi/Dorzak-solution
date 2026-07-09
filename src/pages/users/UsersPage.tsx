import React, { useState } from 'react';
import { useSettingsStore } from '../../stores/settingsStore';
import { useToastStore } from '../../stores/toastStore';
import { AppButton } from '../../components/buttons/AppButton';
import { TextInput } from '../../components/forms/TextInput';
import { ToggleSwitch } from '../../components/forms/ToggleSwitch';
import { SelectInput } from '../../components/forms/SelectInput';
import { AppIcon } from '../../components/icons/AppIcon';

type Role = 'OWNER' | 'MANAGER' | 'CASHIER' | 'VIEWER';

interface StaffMember {
  id: string;
  name: string;
  email: string;
  role: Role;
  active: boolean;
  joinedDate: string;
}

const ROLE_CONFIG: Record<Role, { label: string; color: string; permissions: string[] }> = {
  OWNER: {
    label: 'Owner',
    color: 'var(--dorzak-primary)',
    permissions: ['Full access', 'Billing management', 'Delete account', 'All features'],
  },
  MANAGER: {
    label: 'Manager',
    color: 'var(--dorzak-success)',
    permissions: ['Full access (no billing)', 'Staff management', 'Reports', 'Settings'],
  },
  CASHIER: {
    label: 'Cashier',
    color: 'var(--dorzak-warning)',
    permissions: ['POS Checkout', 'View orders', 'Manage products', 'No settings'],
  },
  VIEWER: {
    label: 'Viewer',
    color: '#8b5cf6',
    permissions: ['View-only access', 'Reports', 'No editing'],
  },
};

export const UsersPage: React.FC = () => {
  const { accountInfo } = useSettingsStore();
  const { addToast } = useToastStore();

  const [showInviteForm, setShowInviteForm] = useState(false);
  const [inviteEmail, setInviteEmail] = useState('');
  const [inviteRole, setInviteRole] = useState<Role>('CASHIER');
  const [inviteName, setInviteName] = useState('');

  const [staff, setStaff] = useState<StaffMember[]>([
    {
      id: 'user_1', name: accountInfo.ownerName, email: accountInfo.email,
      role: 'OWNER', active: true, joinedDate: '2024-01-01',
    },
    {
      id: 'user_2', name: 'Alex Cashier', email: 'alex@example.com',
      role: 'CASHIER', active: true, joinedDate: '2024-06-15',
    },
    {
      id: 'user_3', name: 'Maria Manager', email: 'maria@example.com',
      role: 'MANAGER', active: true, joinedDate: '2024-09-20',
    },
  ]);

  const handleInvite = () => {
    if (!inviteEmail || !inviteName) {
      addToast('Please fill in name and email to send invite', 'danger');
      return;
    }
    const newMember: StaffMember = {
      id: `user_${Date.now()}`,
      name: inviteName,
      email: inviteEmail,
      role: inviteRole,
      active: false,
      joinedDate: new Date().toISOString().slice(0, 10),
    };
    setStaff(prev => [...prev, newMember]);
    setInviteEmail('');
    setInviteName('');
    setShowInviteForm(false);
    addToast(`Invite sent to ${inviteEmail} as ${ROLE_CONFIG[inviteRole].label}!`, 'success');
  };

  const removeStaff = (id: string) => {
    setStaff(prev => prev.filter(s => s.id !== id));
    addToast('Staff member removed', 'info');
  };

  const toggleActive = (id: string) => {
    setStaff(prev => prev.map(s => s.id === id ? { ...s, active: !s.active } : s));
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '24px', maxWidth: '900px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h2 style={{ margin: 0 }}>Users & Staff Management</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Manage team members, assign roles, and control access permissions</span>
        </div>
        <AppButton variant="primary" onClick={() => setShowInviteForm(v => !v)}>
          <AppIcon name={showInviteForm ? 'close' : 'userPlus'} size={16} />
          {showInviteForm ? 'Cancel' : 'Invite Staff Member'}
        </AppButton>
      </div>

      {/* Plan Info */}
      <div style={{
        display: 'flex', gap: '12px', alignItems: 'center',
        padding: '14px 18px', backgroundColor: 'var(--dorzak-primary-light)',
        borderRadius: '10px', border: '1px solid var(--dorzak-primary)'
      }}>
        <AppIcon name="star" size={24} color="var(--color-primary)" />
        <div style={{ flex: 1 }}>
          <strong style={{ color: 'var(--dorzak-primary)', fontSize: '0.9rem' }}>Dorzak Merchant Pro Plan</strong>
          <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
            {staff.length} of unlimited staff users active • Upgrade anytime to add more
          </div>
        </div>
      </div>

      {/* Invite Form */}
      {showInviteForm && (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
          <h3 style={{ display: 'flex', alignItems: 'center', gap: '8px', margin: '0 0 4px 0', fontSize: '1rem' }}><AppIcon name="mail" size={18} />Invite a New Team Member</h3>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
            <TextInput label="Full Name" value={inviteName} onChange={e => setInviteName(e.target.value)} placeholder="e.g. John Smith" />
            <TextInput label="Email Address" type="email" value={inviteEmail} onChange={e => setInviteEmail(e.target.value)} placeholder="john@example.com" />
          </div>
          <SelectInput
            label="Assign Role"
            value={inviteRole}
            onChange={e => setInviteRole(e.target.value as Role)}
            options={[
              { value: 'CASHIER', label: 'Cashier — POS & Orders access' },
              { value: 'MANAGER', label: 'Manager — Full access (no billing)' },
              { value: 'VIEWER', label: 'Viewer — Read-only reports access' },
            ]}
          />
          {/* Role Description */}
          <div style={{ padding: '12px', backgroundColor: 'var(--color-bg)', borderRadius: '8px' }}>
            <strong style={{ fontSize: '0.85rem' }}>{ROLE_CONFIG[inviteRole].label} Permissions:</strong>
            <ul style={{ margin: '6px 0 0 0', paddingLeft: '16px' }}>
              {ROLE_CONFIG[inviteRole].permissions.map(p => (
                <li key={p} style={{ fontSize: '0.82rem', color: 'var(--text-muted)', marginBottom: '3px' }}>{p}</li>
              ))}
            </ul>
          </div>
          <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
            <AppButton variant="secondary" onClick={() => setShowInviteForm(false)}>Cancel</AppButton>
            <AppButton variant="primary" onClick={handleInvite}>Send Invitation</AppButton>
          </div>
        </div>
      )}

      {/* Role Legend */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '10px' }}>
        {(Object.entries(ROLE_CONFIG) as [Role, typeof ROLE_CONFIG.OWNER][]).map(([role, cfg]) => (
          <div key={role} style={{
            padding: '12px', borderRadius: '8px',
            backgroundColor: 'var(--color-bg)',
            border: `2px solid ${cfg.color}33`,
          }}>
            <span style={{ fontSize: '0.75rem', fontWeight: 700, color: cfg.color }}>{cfg.label.toUpperCase()}</span>
            <div style={{ marginTop: '4px' }}>
              {cfg.permissions.slice(0, 2).map(p => (
                <div key={p} style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>• {p}</div>
              ))}
            </div>
          </div>
        ))}
      </div>

      {/* Staff List */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
        {staff.map(member => {
          const roleConfig = ROLE_CONFIG[member.role];
          const isOwner = member.role === 'OWNER';
          return (
            <div key={member.id} style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              padding: '16px 18px',
              backgroundColor: 'var(--color-surface)',
              borderRadius: '12px',
              border: '1px solid var(--color-border)',
              opacity: member.active ? 1 : 0.6,
            }}>
              <div style={{ display: 'flex', gap: '14px', alignItems: 'center' }}>
                <div style={{
                  width: '42px', height: '42px', borderRadius: '50%',
                  backgroundColor: `${roleConfig.color}22`,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  fontWeight: 800, color: roleConfig.color, fontSize: '1.1rem'
                }}>
                  {member.name[0]}
                </div>
                <div>
                  <div style={{ fontWeight: 600, fontSize: '0.95rem' }}>
                    {member.name}{isOwner ? ' (You)' : ''}
                  </div>
                  <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>
                    {member.email} • Joined {member.joinedDate}
                  </div>
                  {!member.active && (
                    <span style={{ fontSize: '0.72rem', color: 'var(--dorzak-warning)', fontWeight: 600 }}>Invite pending</span>
                  )}
                </div>
              </div>

              <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
                <span style={{
                  padding: '4px 12px', borderRadius: '999px', fontSize: '0.75rem', fontWeight: 700,
                  backgroundColor: `${roleConfig.color}22`, color: roleConfig.color
                }}>
                  {roleConfig.label}
                </span>
                {!isOwner && (
                  <>
                    <ToggleSwitch checked={member.active} onChange={() => toggleActive(member.id)} />
                    <button
                      type="button"
                      onClick={() => removeStaff(member.id)}
                      style={{ color: 'var(--dorzak-danger)', fontSize: '0.85rem', fontWeight: 600 }}
                    >
                      Remove
                    </button>
                  </>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
