import React, { useCallback, useEffect, useState } from 'react';
import { platformApi } from '../../api/endpoints';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon } from '../../components/icons/AppIcon';
import { useToastStore } from '../../stores/toastStore';

// ─── Types ──────────────────────────────────────────────────────────────────

interface PlanFeatureRow { feature: string; limit_value: number | null; }

interface Plan {
  id: number;
  code: string;
  name_en: string;
  name_ar: string;
  price: number;
  billing_cycle: string;
  is_default: boolean;
  is_active: boolean;
  sort_order: number;
  features: PlanFeatureRow[];
}

interface StoreRow {
  id: number;
  name: string;
  suspended_at: string | null;
  subscription?: { plan?: { code: string; name_en: string } };
}

// ─── Plans tab ───────────────────────────────────────────────────────────────

const PlansTab: React.FC = () => {
  const { addToast } = useToastStore();
  const [plans, setPlans] = useState<Plan[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editPrice, setEditPrice] = useState('');
  const [editActive, setEditActive] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const res = (await platformApi.plans.list()) as any;
      setPlans(res.data ?? []);
    } catch {
      addToast('Failed to load plans', 'danger');
    } finally {
      setLoading(false);
    }
  }, [addToast]);

  useEffect(() => { load(); }, [load]);

  const startEdit = (p: Plan) => {
    setEditingId(p.id);
    setEditPrice(String(p.price));
    setEditActive(p.is_active);
  };

  const cancelEdit = () => setEditingId(null);

  const saveEdit = async (p: Plan) => {
    try {
      await platformApi.plans.update(p.id, { price: parseFloat(editPrice), is_active: editActive });
      addToast(`${p.name_en} updated`, 'success');
      setEditingId(null);
      load();
    } catch {
      addToast('Update failed', 'danger');
    }
  };

  if (loading) return <div style={{ color: 'var(--text-muted)', padding: '24px' }}>Loading plans…</div>;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
      {plans.map((p) => (
        <div key={p.id} className="card" style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
              <span style={{ fontWeight: 700, fontSize: '1rem' }}>{p.name_en}</span>
              <span style={{ fontSize: '0.72rem', fontFamily: 'monospace', background: 'var(--bg-subtle)', padding: '2px 6px', borderRadius: '4px' }}>{p.code}</span>
              {p.is_default && <span style={{ fontSize: '0.72rem', color: 'var(--dorzak-primary)', fontWeight: 600 }}>DEFAULT</span>}
              {!p.is_active && <span style={{ fontSize: '0.72rem', color: 'var(--dorzak-warning)', fontWeight: 600 }}>INACTIVE</span>}
            </div>
            {editingId === p.id ? (
              <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                <label style={{ fontSize: '0.8rem', display: 'flex', alignItems: 'center', gap: '4px' }}>
                  <input type="checkbox" checked={editActive} onChange={(e) => setEditActive(e.target.checked)} />
                  Active
                </label>
                <input
                  type="number"
                  value={editPrice}
                  onChange={(e) => setEditPrice(e.target.value)}
                  style={{ width: '80px', padding: '4px 8px', border: '1px solid var(--border)', borderRadius: '6px', fontSize: '0.9rem' }}
                />
                <AppButton variant="primary" onClick={() => saveEdit(p)}>Save</AppButton>
                <AppButton variant="tertiary" onClick={cancelEdit}>Cancel</AppButton>
              </div>
            ) : (
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <span style={{ fontWeight: 700, fontSize: '1.05rem' }}>
                  {p.price === 0 ? 'Free' : `${p.price} / ${p.billing_cycle}`}
                </span>
                <AppButton variant="tertiary" onClick={() => startEdit(p)}>
                  <AppIcon name="settings" size={14} /> Edit
                </AppButton>
              </div>
            )}
          </div>
          {p.features.length > 0 && (
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '6px' }}>
              {p.features.map((f, i) => (
                <span key={i} style={{ fontSize: '0.73rem', padding: '2px 8px', borderRadius: '12px', background: 'var(--dorzak-primary-light)', color: 'var(--dorzak-primary)', fontWeight: 600 }}>
                  {f.feature}{f.limit_value != null ? ` (${f.limit_value})` : ''}
                </span>
              ))}
            </div>
          )}
        </div>
      ))}
    </div>
  );
};

// ─── Stores tab ───────────────────────────────────────────────────────────────

const StoresTab: React.FC = () => {
  const { addToast } = useToastStore();
  const [stores, setStores] = useState<StoreRow[]>([]);
  const [plans, setPlans] = useState<Plan[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState<'' | 'active' | 'suspended'>('');
  const [assigningId, setAssigningId] = useState<number | null>(null);
  const [selectedPlanId, setSelectedPlanId] = useState<number | ''>('');

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const params: Record<string, string> = {};
      if (search) params.search = search;
      if (statusFilter) params.status = statusFilter;
      const [storesRes, plansRes] = await Promise.all([
        platformApi.stores.list(params) as any,
        platformApi.plans.list() as any,
      ]);
      setStores(storesRes.data ?? []);
      setPlans(plansRes.data ?? []);
    } catch {
      addToast('Failed to load stores', 'danger');
    } finally {
      setLoading(false);
    }
  }, [search, statusFilter, addToast]);

  useEffect(() => { load(); }, [load]);

  const suspend = async (id: number) => {
    try {
      await platformApi.stores.suspend(id);
      addToast('Store suspended', 'success');
      load();
    } catch { addToast('Failed', 'danger'); }
  };

  const reactivate = async (id: number) => {
    try {
      await platformApi.stores.reactivate(id);
      addToast('Store reactivated', 'success');
      load();
    } catch { addToast('Failed', 'danger'); }
  };

  const assignPlan = async (storeId: number) => {
    if (!selectedPlanId) return;
    try {
      await platformApi.stores.assignPlan(storeId, Number(selectedPlanId));
      addToast('Plan assigned', 'success');
      setAssigningId(null);
      setSelectedPlanId('');
      load();
    } catch { addToast('Failed', 'danger'); }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
      <div style={{ display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap' }}>
        <input
          type="search"
          placeholder="Search stores…"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          style={{ flex: 1, minWidth: '180px', padding: '8px 12px', border: '1px solid var(--border)', borderRadius: '8px', fontSize: '0.9rem' }}
        />
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value as '' | 'active' | 'suspended')}
          style={{ padding: '8px 12px', border: '1px solid var(--border)', borderRadius: '8px', fontSize: '0.9rem' }}
        >
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>

      {loading ? (
        <div style={{ color: 'var(--text-muted)', padding: '24px' }}>Loading stores…</div>
      ) : stores.length === 0 ? (
        <div style={{ color: 'var(--text-muted)', padding: '24px', textAlign: 'center' }}>No stores found.</div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
          {stores.map((s) => {
            const isSuspended = s.suspended_at != null;
            const planLabel = s.subscription?.plan?.code ?? '—';
            return (
              <div key={s.id} className="card" style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap', borderLeft: isSuspended ? '4px solid var(--dorzak-error)' : '4px solid transparent' }}>
                <div style={{ flex: 1, minWidth: '120px' }}>
                  <div style={{ fontWeight: 600 }}>{s.name}</div>
                  <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>ID {s.id} · {isSuspended ? <span style={{ color: 'var(--dorzak-error)' }}>Suspended</span> : <span style={{ color: 'var(--dorzak-success)' }}>Active</span>}</div>
                </div>

                <span style={{ fontSize: '0.78rem', fontFamily: 'monospace', background: 'var(--bg-subtle)', padding: '2px 8px', borderRadius: '4px' }}>{planLabel}</span>

                {assigningId === s.id ? (
                  <div style={{ display: 'flex', gap: '6px', alignItems: 'center' }}>
                    <select
                      value={selectedPlanId}
                      onChange={(e) => setSelectedPlanId(e.target.value ? Number(e.target.value) : '')}
                      style={{ padding: '4px 8px', border: '1px solid var(--border)', borderRadius: '6px', fontSize: '0.85rem' }}
                    >
                      <option value="">Select plan…</option>
                      {plans.map((p) => <option key={p.id} value={p.id}>{p.name_en} ({p.code})</option>)}
                    </select>
                    <AppButton variant="primary" onClick={() => assignPlan(s.id)}>Assign</AppButton>
                    <AppButton variant="tertiary" onClick={() => { setAssigningId(null); setSelectedPlanId(''); }}>Cancel</AppButton>
                  </div>
                ) : (
                  <div style={{ display: 'flex', gap: '6px' }}>
                    <AppButton variant="tertiary" onClick={() => { setAssigningId(s.id); setSelectedPlanId(''); }}>
                      Set plan
                    </AppButton>
                    {isSuspended ? (
                      <AppButton variant="secondary" onClick={() => reactivate(s.id)}>Reactivate</AppButton>
                    ) : (
                      <AppButton variant="tertiary" onClick={() => suspend(s.id)} style={{ color: 'var(--dorzak-error)' }}>Suspend</AppButton>
                    )}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};

// ─── Page shell ──────────────────────────────────────────────────────────────

type Tab = 'plans' | 'stores';

export const PlatformPage: React.FC = () => {
  const [tab, setTab] = useState<Tab>('stores');

  const tabStyle = (active: boolean): React.CSSProperties => ({
    padding: '8px 20px',
    border: 'none',
    borderBottom: active ? '2px solid var(--dorzak-primary)' : '2px solid transparent',
    background: 'none',
    cursor: 'pointer',
    fontWeight: active ? 700 : 400,
    color: active ? 'var(--dorzak-primary)' : 'var(--text-muted)',
    fontSize: '0.95rem',
  });

  return (
    <div style={{ maxWidth: '960px', display: 'flex', flexDirection: 'column', gap: '24px' }}>
      <div>
        <h2 style={{ margin: 0 }}>Platform Admin</h2>
        <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Manage plans and merchant stores across the platform</span>
      </div>

      <div style={{ borderBottom: '1px solid var(--border)', display: 'flex', gap: '4px' }}>
        <button style={tabStyle(tab === 'stores')} onClick={() => setTab('stores')}>Stores</button>
        <button style={tabStyle(tab === 'plans')} onClick={() => setTab('plans')}>Plans</button>
      </div>

      {tab === 'stores' ? <StoresTab /> : <PlansTab />}
    </div>
  );
};
