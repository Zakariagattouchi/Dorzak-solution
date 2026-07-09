import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { platformApi } from '../../api/endpoints';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon } from '../../components/icons/AppIcon';
import { useAuthStore } from '../../stores/authStore';
import { useToastStore } from '../../stores/toastStore';

// ─── Types ──────────────────────────────────────────────────────────────────

interface PlanFeatureRow { feature: string; limit_value: number | null; }

interface Plan {
  id: number;
  code: string;
  name_en: string;
  name_ar: string;
  description_en?: string | null;
  price: number;
  billing_cycle: string;
  trial_days?: number;
  is_default: boolean;
  is_active: boolean;
  sort_order: number;
  features: PlanFeatureRow[];
}

interface StoreRow {
  id: number;
  name: string;
  suspended_at: string | null;
  plan?: { id: number; code: string; name_en: string } | null;
}

interface StoreDetail extends StoreRow {
  email: string | null;
  created_at: string | null;
  owner: { id: number; name: string; email: string } | null;
  metrics: { staff: number; products: number; customers: number; orders: number; revenue: number };
}

interface UserRow {
  id: number;
  name: string;
  email: string;
  is_platform_admin: boolean;
  is_active: boolean;
  memberships: { store_id: number; store_name: string | null; role: string; is_active: boolean }[];
}

interface AuditRow {
  id: number;
  action: string;
  admin: { name: string; email: string } | null;
  target_label: string | null;
  ip_address: string | null;
  created_at: string | null;
}

// ─── Shared bits ──────────────────────────────────────────────────────────────

const inputStyle: React.CSSProperties = { padding: '8px 12px', border: '1px solid var(--color-border)', borderRadius: '8px', fontSize: '0.9rem' };
const codePill = (text: string): React.CSSProperties => ({ fontSize: '0.72rem', fontFamily: 'monospace', background: 'var(--color-bg)', padding: '2px 8px', borderRadius: '4px' });
const Loading: React.FC = () => <div style={{ color: 'var(--text-muted)', padding: '24px' }}>Loading…</div>;

// ─── Overview tab ─────────────────────────────────────────────────────────────

const Metric: React.FC<{ label: string; value: string | number; accent?: string }> = ({ label, value, accent }) => (
  <div className="card" style={{ flex: '1 1 140px', minWidth: '140px' }}>
    <div style={{ fontSize: '1.8rem', fontWeight: 800, color: accent }}>{value}</div>
    <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)', textTransform: 'uppercase', letterSpacing: '0.04em' }}>{label}</div>
  </div>
);

const OverviewTab: React.FC = () => {
  const { addToast } = useToastStore();
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (platformApi.overview() as Promise<any>)
      .then((r) => setData(r.data))
      .catch(() => addToast('Failed to load overview', 'danger'))
      .finally(() => setLoading(false));
  }, [addToast]);

  if (loading) return <Loading />;
  if (!data) return null;

  const maxSignup = Math.max(1, ...data.signups_last_14_days.map((d: any) => d.count));

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '12px' }}>
        <Metric label="Stores" value={data.stores.total} />
        <Metric label="Active" value={data.stores.active} accent="var(--dorzak-success)" />
        <Metric label="Suspended" value={data.stores.suspended} accent="var(--dorzak-error)" />
        <Metric label="Trials live" value={data.trials_active} accent="var(--dorzak-primary)" />
        <Metric label="Est. MRR" value={data.mrr_estimate.toLocaleString()} />
        <Metric label="Users" value={data.users_total} />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '16px' }}>
        <div className="card">
          <h4 style={{ margin: '0 0 12px' }}>Plan distribution</h4>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
            {data.plan_distribution.map((p: any) => (
              <div key={p.code} style={{ display: 'flex', alignItems: 'center', gap: '10px', fontSize: '0.85rem' }}>
                <span style={{ ...codePill(p.code), minWidth: '90px' }}>{p.code}</span>
                <div style={{ flex: 1, height: '8px', background: 'var(--color-bg)', borderRadius: '4px', overflow: 'hidden' }}>
                  <div style={{ width: `${(p.stores / Math.max(1, data.stores.total)) * 100}%`, height: '100%', background: 'var(--dorzak-primary)' }} />
                </div>
                <strong style={{ minWidth: '28px', textAlign: 'right' }}>{p.stores}</strong>
              </div>
            ))}
          </div>
        </div>

        <div className="card">
          <h4 style={{ margin: '0 0 12px' }}>Signups — last 14 days</h4>
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: '4px', height: '80px' }}>
            {data.signups_last_14_days.map((d: any) => (
              <div key={d.date} title={`${d.date}: ${d.count}`} style={{ flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'flex-end', height: '100%' }}>
                <div style={{ height: `${(d.count / maxSignup) * 100}%`, minHeight: d.count ? '3px' : '0', background: 'var(--dorzak-primary)', borderRadius: '3px 3px 0 0' }} />
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

// ─── Stores tab ───────────────────────────────────────────────────────────────

const StoresTab: React.FC = () => {
  const { addToast } = useToastStore();
  const { impersonate } = useAuthStore();
  const navigate = useNavigate();
  const [stores, setStores] = useState<StoreRow[]>([]);
  const [plans, setPlans] = useState<Plan[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState<'' | 'active' | 'suspended'>('');
  const [assigningId, setAssigningId] = useState<number | null>(null);
  const [selectedPlanId, setSelectedPlanId] = useState<number | ''>('');
  const [detail, setDetail] = useState<StoreDetail | null>(null);
  const [detailId, setDetailId] = useState<number | null>(null);
  const [deleteName, setDeleteName] = useState('');

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

  const openDetail = async (id: number) => {
    if (detailId === id) { setDetailId(null); setDetail(null); return; }
    setDetailId(id);
    setDetail(null);
    setDeleteName('');
    try {
      const res = (await platformApi.stores.show(id)) as any;
      setDetail(res.data);
    } catch { addToast('Failed to load store detail', 'danger'); }
  };

  const act = async (fn: Promise<unknown>, ok: string) => {
    try { await fn; addToast(ok, 'success'); load(); }
    catch (e: any) { addToast(e?.message ?? 'Action failed', 'danger'); }
  };

  const assignPlan = async (storeId: number) => {
    if (!selectedPlanId) return;
    await act(platformApi.stores.assignPlan(storeId, Number(selectedPlanId)), 'Plan assigned');
    setAssigningId(null); setSelectedPlanId('');
  };

  const openStore = async (id: number, name: string) => {
    try {
      await impersonate(id);
      addToast(`Now viewing ${name}`, 'info');
      navigate('/checkout');
    } catch (e: any) {
      addToast(e?.message ?? 'Could not open store', 'danger');
    }
  };

  const deleteStore = async (store: StoreDetail) => {
    try {
      await platformApi.stores.destroy(store.id, deleteName);
      addToast(`${store.name} deleted`, 'success');
      setDetailId(null); setDetail(null); load();
    } catch (e: any) {
      addToast(e?.message ?? 'Delete failed', 'danger');
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
      <div style={{ display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap' }}>
        <input type="search" placeholder="Search stores…" value={search} onChange={(e) => setSearch(e.target.value)} style={{ ...inputStyle, flex: 1, minWidth: '180px' }} />
        <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value as any)} style={inputStyle}>
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>

      {loading ? <Loading /> : stores.length === 0 ? (
        <div style={{ color: 'var(--text-muted)', padding: '24px', textAlign: 'center' }}>No stores found.</div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
          {stores.map((s) => {
            const isSuspended = s.suspended_at != null;
            const isOpen = detailId === s.id;
            return (
              <div key={s.id} className="card" style={{ borderLeft: isSuspended ? '4px solid var(--dorzak-error)' : '4px solid transparent' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                  <button onClick={() => openDetail(s.id)} style={{ flex: 1, minWidth: '120px', textAlign: 'left', background: 'none', border: 'none', cursor: 'pointer', padding: 0 }}>
                    <div style={{ fontWeight: 600 }}>{s.name}</div>
                    <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>
                      ID {s.id} · {isSuspended ? <span style={{ color: 'var(--dorzak-error)' }}>Suspended</span> : <span style={{ color: 'var(--dorzak-success)' }}>Active</span>}
                    </div>
                  </button>

                  <span style={codePill(s.plan?.code ?? '—')}>{s.plan?.code ?? '—'}</span>

                  {assigningId === s.id ? (
                    <div style={{ display: 'flex', gap: '6px', alignItems: 'center' }}>
                      <select value={selectedPlanId} onChange={(e) => setSelectedPlanId(e.target.value ? Number(e.target.value) : '')} style={{ ...inputStyle, padding: '4px 8px', fontSize: '0.85rem' }}>
                        <option value="">Select plan…</option>
                        {plans.map((p) => <option key={p.id} value={p.id}>{p.name_en} ({p.code})</option>)}
                      </select>
                      <AppButton variant="primary" onClick={() => assignPlan(s.id)}>Assign</AppButton>
                      <AppButton variant="tertiary" onClick={() => { setAssigningId(null); setSelectedPlanId(''); }}>Cancel</AppButton>
                    </div>
                  ) : (
                    <div style={{ display: 'flex', gap: '6px' }}>
                      <AppButton variant="secondary" onClick={() => openStore(s.id, s.name)}>
                        <AppIcon name="eye" size={14} /> Open
                      </AppButton>
                      <AppButton variant="tertiary" onClick={() => { setAssigningId(s.id); setSelectedPlanId(''); }}>Set plan</AppButton>
                      {isSuspended
                        ? <AppButton variant="secondary" onClick={() => act(platformApi.stores.reactivate(s.id), 'Store reactivated')}>Reactivate</AppButton>
                        : <AppButton variant="tertiary" onClick={() => act(platformApi.stores.suspend(s.id), 'Store suspended')} style={{ color: 'var(--dorzak-error)' }}>Suspend</AppButton>}
                    </div>
                  )}
                </div>

                {isOpen && (
                  <div style={{ marginTop: '12px', paddingTop: '12px', borderTop: '1px solid var(--color-border)' }}>
                    {!detail ? <Loading /> : (
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                        <div style={{ fontSize: '0.85rem' }}>
                          <strong>Owner:</strong> {detail.owner ? `${detail.owner.name} · ${detail.owner.email}` : '—'}
                          {detail.created_at && <span style={{ color: 'var(--text-muted)' }}> · joined {new Date(detail.created_at).toLocaleDateString()}</span>}
                        </div>
                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '16px', fontSize: '0.85rem' }}>
                          <span><strong>{detail.metrics.staff}</strong> staff</span>
                          <span><strong>{detail.metrics.products}</strong> products</span>
                          <span><strong>{detail.metrics.customers}</strong> customers</span>
                          <span><strong>{detail.metrics.orders}</strong> orders</span>
                          <span><strong>{detail.metrics.revenue.toLocaleString()}</strong> revenue</span>
                        </div>
                        <div style={{ display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap', paddingTop: '8px' }}>
                          <input placeholder={`Type "${detail.name}" to delete`} value={deleteName} onChange={(e) => setDeleteName(e.target.value)} style={{ ...inputStyle, flex: 1, minWidth: '200px' }} />
                          <AppButton variant="danger" disabled={deleteName !== detail.name} onClick={() => deleteStore(detail)}>
                            <AppIcon name="trash" size={14} /> Delete store
                          </AppButton>
                        </div>
                      </div>
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

// ─── Users tab ────────────────────────────────────────────────────────────────

const UsersTab: React.FC = () => {
  const { addToast } = useToastStore();
  const [users, setUsers] = useState<UserRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [adminsOnly, setAdminsOnly] = useState(false);
  const [tempPasswords, setTempPasswords] = useState<Record<number, string>>({});

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const params: Record<string, string> = {};
      if (search) params.search = search;
      if (adminsOnly) params.filter = 'admins';
      const res = (await platformApi.users.list(params)) as any;
      setUsers(res.data ?? []);
    } catch { addToast('Failed to load users', 'danger'); }
    finally { setLoading(false); }
  }, [search, adminsOnly, addToast]);

  useEffect(() => { load(); }, [load]);

  const act = async (fn: Promise<unknown>, ok: string) => {
    try { await fn; addToast(ok, 'success'); load(); }
    catch (e: any) { addToast(e?.message ?? 'Action failed', 'danger'); }
  };

  const resetPassword = async (u: UserRow) => {
    try {
      const res = (await platformApi.users.resetPassword(u.id)) as any;
      setTempPasswords((prev) => ({ ...prev, [u.id]: res.data.temporary_password }));
      addToast(`Password reset for ${u.email}`, 'success');
    } catch (e: any) { addToast(e?.message ?? 'Reset failed', 'danger'); }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
      <div style={{ display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap' }}>
        <input type="search" placeholder="Search users…" value={search} onChange={(e) => setSearch(e.target.value)} style={{ ...inputStyle, flex: 1, minWidth: '180px' }} />
        <label style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '0.85rem' }}>
          <input type="checkbox" checked={adminsOnly} onChange={(e) => setAdminsOnly(e.target.checked)} /> Admins only
        </label>
      </div>

      {loading ? <Loading /> : users.length === 0 ? (
        <div style={{ color: 'var(--text-muted)', padding: '24px', textAlign: 'center' }}>No users found.</div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
          {users.map((u) => (
            <div key={u.id} className="card" style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                <div style={{ flex: 1, minWidth: '160px' }}>
                  <div style={{ fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px' }}>
                    {u.name}
                    {u.is_platform_admin && <span style={{ fontSize: '0.68rem', fontWeight: 700, color: '#fff', background: 'var(--dorzak-primary)', padding: '1px 6px', borderRadius: '10px' }}>SUPER ADMIN</span>}
                    {!u.is_active && <span style={{ fontSize: '0.68rem', fontWeight: 700, color: 'var(--dorzak-error)' }}>DISABLED</span>}
                  </div>
                  <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>{u.email}</div>
                  <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', marginTop: '2px' }}>
                    {u.memberships.length === 0 ? 'No stores' : u.memberships.map((m) => `${m.store_name} (${m.role})`).join(' · ')}
                  </div>
                </div>
                <div style={{ display: 'flex', gap: '6px', flexWrap: 'wrap' }}>
                  {u.is_platform_admin
                    ? <AppButton variant="tertiary" onClick={() => act(platformApi.users.revokeAdmin(u.id), 'Admin revoked')}>Revoke admin</AppButton>
                    : <AppButton variant="tertiary" onClick={() => act(platformApi.users.grantAdmin(u.id), 'Admin granted')}>Make admin</AppButton>}
                  {u.is_active
                    ? <AppButton variant="tertiary" onClick={() => act(platformApi.users.setActive(u.id, false), 'User disabled')} style={{ color: 'var(--dorzak-error)' }}>Disable</AppButton>
                    : <AppButton variant="secondary" onClick={() => act(platformApi.users.setActive(u.id, true), 'User enabled')}>Enable</AppButton>}
                  <AppButton variant="tertiary" onClick={() => resetPassword(u)}>Reset password</AppButton>
                </div>
              </div>
              {tempPasswords[u.id] && (
                <div style={{ fontSize: '0.8rem', background: 'var(--color-bg)', padding: '8px 12px', borderRadius: '6px' }}>
                  Temporary password (shown once): <strong style={{ fontFamily: 'monospace' }}>{tempPasswords[u.id]}</strong> — relay it securely; their existing sessions were signed out.
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

// ─── Audit tab ────────────────────────────────────────────────────────────────

const AuditTab: React.FC = () => {
  const { addToast } = useToastStore();
  const [logs, setLogs] = useState<AuditRow[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (platformApi.auditLogs() as Promise<any>)
      .then((r) => setLogs(r.data ?? []))
      .catch(() => addToast('Failed to load audit log', 'danger'))
      .finally(() => setLoading(false));
  }, [addToast]);

  if (loading) return <Loading />;
  if (logs.length === 0) return <div style={{ color: 'var(--text-muted)', padding: '24px', textAlign: 'center' }}>No activity recorded yet.</div>;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
      {logs.map((log) => (
        <div key={log.id} className="card" style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap', fontSize: '0.82rem', padding: '10px 14px' }}>
          <span style={{ ...codePill(log.action), color: 'var(--dorzak-primary)' }}>{log.action}</span>
          <span style={{ flex: 1, minWidth: '140px' }}>{log.target_label ?? '—'}</span>
          <span style={{ color: 'var(--text-muted)' }}>{log.admin?.email ?? 'system'}</span>
          <span style={{ color: 'var(--text-muted)' }}>{log.created_at ? new Date(log.created_at).toLocaleString() : ''}</span>
        </div>
      ))}
    </div>
  );
};

// ─── Page shell ──────────────────────────────────────────────────────────────

type Tab = 'overview' | 'stores' | 'users' | 'plans' | 'audit';
const TABS: { key: Tab; label: string }[] = [
  { key: 'overview', label: 'Overview' },
  { key: 'stores', label: 'Stores' },
  { key: 'users', label: 'Users' },
  { key: 'plans', label: 'Plans' },
  { key: 'audit', label: 'Audit log' },
];

interface FeatureMeta {
  key: string;
  kind: 'toggle' | 'limit';
  label: string;
  description: string;
  group: string;
  enforced: boolean;
  unit: string | null;
}

const blankPlan: Plan = {
  id: 0, code: '', name_en: '', name_ar: '', price: 0, billing_cycle: 'monthly',
  is_default: false, is_active: true, sort_order: 0, features: [],
};

// Full create/edit form with the feature matrix.
const PlanEditor: React.FC<{ plan: Plan; catalog: FeatureMeta[]; onSaved: () => void; onCancel: () => void }> = ({ plan, catalog, onSaved, onCancel }) => {
  const { addToast } = useToastStore();
  const isNew = plan.id === 0;
  const [code, setCode] = useState(plan.code);
  const [nameEn, setNameEn] = useState(plan.name_en);
  const [nameAr, setNameAr] = useState(plan.name_ar || '');
  const [descEn, setDescEn] = useState(plan.description_en || '');
  const [price, setPrice] = useState(String(plan.price));
  const [cycle, setCycle] = useState(plan.billing_cycle || 'monthly');
  const [trialDays, setTrialDays] = useState(String((plan as any).trial_days ?? 0));
  const [isActive, setIsActive] = useState(plan.is_active);
  const [saving, setSaving] = useState(false);

  // toggles: key -> on; limits: key -> cap string ('' = unlimited)
  const [toggles, setToggles] = useState<Record<string, boolean>>({});
  const [limits, setLimits] = useState<Record<string, string>>({});

  useEffect(() => {
    const t: Record<string, boolean> = {};
    const l: Record<string, string> = {};
    for (const f of catalog) {
      const row = plan.features.find((r) => r.feature === f.key);
      if (f.kind === 'toggle') t[f.key] = !!row;
      else l[f.key] = row && row.limit_value != null ? String(row.limit_value) : '';
    }
    setToggles(t); setLimits(l);
  }, [plan, catalog]);

  const groups = Array.from(new Set(catalog.map((f) => f.group)));

  const save = async () => {
    setSaving(true);
    const features = [
      ...catalog.filter((f) => f.kind === 'toggle' && toggles[f.key]).map((f) => ({ feature: f.key })),
      ...catalog.filter((f) => f.kind === 'limit' && limits[f.key] !== '').map((f) => ({ feature: f.key, limit_value: Number(limits[f.key]) })),
    ];
    const payload: Record<string, unknown> = {
      code, name_en: nameEn, name_ar: nameAr, description_en: descEn || null,
      price: parseFloat(price) || 0, billing_cycle: cycle, trial_days: parseInt(trialDays, 10) || 0,
      is_active: isActive, features,
    };
    try {
      if (isNew) await platformApi.plans.create(payload);
      else await platformApi.plans.update(plan.id, payload);
      addToast(isNew ? 'Plan created' : `${nameEn} updated`, 'success');
      onSaved();
    } catch (e: any) {
      const errs = e?.errors ? Object.values(e.errors).flat().join(' ') : null;
      addToast(errs ?? e?.message ?? 'Save failed', 'danger');
    } finally { setSaving(false); }
  };

  const field = (label: string, node: React.ReactNode) => (
    <label style={{ display: 'flex', flexDirection: 'column', gap: '4px', fontSize: '0.8rem', fontWeight: 600 }}>
      {label}{node}
    </label>
  );

  return (
    <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '18px' }}>
      <h3 style={{ margin: 0 }}>{isNew ? 'New plan' : `Edit ${plan.name_en}`}</h3>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: '12px' }}>
        {field('Code', <input value={code} onChange={(e) => setCode(e.target.value.toUpperCase())} disabled={plan.is_default} style={inputStyle} placeholder="STARTER" />)}
        {field('Name (EN)', <input value={nameEn} onChange={(e) => setNameEn(e.target.value)} style={inputStyle} />)}
        {field('Name (AR)', <input value={nameAr} onChange={(e) => setNameAr(e.target.value)} dir="rtl" style={inputStyle} />)}
        {field('Price', <input type="number" value={price} onChange={(e) => setPrice(e.target.value)} style={inputStyle} />)}
        {field('Billing cycle', (
          <select value={cycle} onChange={(e) => setCycle(e.target.value)} style={inputStyle}>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
            <option value="once">One-time</option>
          </select>
        ))}
        {field('Free trial (days)', <input type="number" value={trialDays} onChange={(e) => setTrialDays(e.target.value)} style={inputStyle} />)}
      </div>
      {field('Description (EN)', <input value={descEn} onChange={(e) => setDescEn(e.target.value)} style={inputStyle} placeholder="Short pitch shown on the pricing page" />)}
      <label style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '0.85rem' }}>
        <input type="checkbox" checked={isActive} onChange={(e) => setIsActive(e.target.checked)} /> Active (shown on the upgrade page)
      </label>

      {/* Feature matrix */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
        {groups.map((group) => (
          <div key={group}>
            <div style={{ fontSize: '0.75rem', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--text-muted)', marginBottom: '8px' }}>{group}</div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
              {catalog.filter((f) => f.group === group).map((f) => (
                <div key={f.key} style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                  <div style={{ flex: 1, minWidth: '200px' }}>
                    <div style={{ fontSize: '0.88rem', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '6px' }}>
                      {f.label}
                      {!f.enforced && <span title="Configurable, not server-enforced yet" style={{ fontSize: '0.62rem', color: 'var(--text-muted)', border: '1px solid var(--color-border)', borderRadius: '8px', padding: '0 5px' }}>soft</span>}
                    </div>
                    <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{f.description}</div>
                  </div>
                  {f.kind === 'toggle' ? (
                    <label style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '0.8rem' }}>
                      <input type="checkbox" checked={!!toggles[f.key]} onChange={(e) => setToggles((p) => ({ ...p, [f.key]: e.target.checked }))} />
                      {toggles[f.key] ? 'On' : 'Off'}
                    </label>
                  ) : (
                    <input
                      type="number" min="1" placeholder="Unlimited"
                      value={limits[f.key] ?? ''}
                      onChange={(e) => setLimits((p) => ({ ...p, [f.key]: e.target.value }))}
                      style={{ ...inputStyle, width: '120px' }}
                    />
                  )}
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>

      <div style={{ display: 'flex', gap: '8px' }}>
        <AppButton variant="primary" loading={saving} onClick={save}>{isNew ? 'Create plan' : 'Save changes'}</AppButton>
        <AppButton variant="tertiary" onClick={onCancel}>Cancel</AppButton>
      </div>
    </div>
  );
};

const PlansTab: React.FC = () => {
  const { addToast } = useToastStore();
  const [plans, setPlans] = useState<Plan[]>([]);
  const [catalog, setCatalog] = useState<FeatureMeta[]>([]);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState<Plan | null>(null);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const [plansRes, catRes] = await Promise.all([
        platformApi.plans.list() as any,
        platformApi.planFeatures() as any,
      ]);
      setPlans(plansRes.data ?? []);
      setCatalog(catRes.data ?? []);
    } catch { addToast('Failed to load plans', 'danger'); }
    finally { setLoading(false); }
  }, [addToast]);

  useEffect(() => { load(); }, [load]);

  const deletePlan = async (p: Plan) => {
    if (!confirm(`Delete the ${p.name_en} plan?`)) return;
    try { await platformApi.plans.destroy(p.id); addToast('Plan deleted', 'success'); load(); }
    catch (e: any) { addToast(e?.message ?? 'Delete failed', 'danger'); }
  };

  const labelFor = (key: string) => catalog.find((f) => f.key === key)?.label ?? key;

  if (loading) return <Loading />;
  if (editing) return <PlanEditor plan={editing} catalog={catalog} onSaved={() => { setEditing(null); load(); }} onCancel={() => setEditing(null)} />;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
      <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
        <AppButton variant="primary" onClick={() => setEditing({ ...blankPlan })}>
          <AppIcon name="plus" size={14} /> New plan
        </AppButton>
      </div>
      {plans.map((p) => (
        <div key={p.id} className="card" style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', justifyContent: 'space-between', flexWrap: 'wrap' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
              <span style={{ fontWeight: 700, fontSize: '1rem' }}>{p.name_en}</span>
              <span style={codePill(p.code)}>{p.code}</span>
              {p.is_default && <span style={{ fontSize: '0.72rem', color: 'var(--dorzak-primary)', fontWeight: 600 }}>DEFAULT</span>}
              {!p.is_active && <span style={{ fontSize: '0.72rem', color: 'var(--dorzak-warning)', fontWeight: 600 }}>INACTIVE</span>}
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <span style={{ fontWeight: 700, fontSize: '1.05rem' }}>{p.price === 0 ? 'Free' : `${p.price} / ${p.billing_cycle}`}</span>
              <AppButton variant="tertiary" onClick={() => setEditing(p)}>
                <AppIcon name="settings" size={14} /> Edit
              </AppButton>
              {!p.is_default && <AppButton variant="tertiary" onClick={() => deletePlan(p)} style={{ color: 'var(--dorzak-error)' }}>Delete</AppButton>}
            </div>
          </div>
          {p.features.length > 0 && (
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '6px' }}>
              {p.features.map((f, i) => (
                <span key={i} style={{ fontSize: '0.73rem', padding: '2px 8px', borderRadius: '12px', background: 'var(--dorzak-primary-light)', color: 'var(--dorzak-primary)', fontWeight: 600 }}>
                  {labelFor(f.feature)}{f.limit_value != null ? `: ${f.limit_value}` : ''}
                </span>
              ))}
            </div>
          )}
        </div>
      ))}
    </div>
  );
};

export const PlatformPage: React.FC = () => {
  const [tab, setTab] = useState<Tab>('overview');

  const tabStyle = (active: boolean): React.CSSProperties => ({
    padding: '8px 18px',
    border: 'none',
    borderBottom: active ? '2px solid var(--dorzak-primary)' : '2px solid transparent',
    background: 'none',
    cursor: 'pointer',
    fontWeight: active ? 700 : 400,
    color: active ? 'var(--dorzak-primary)' : 'var(--text-muted)',
    fontSize: '0.95rem',
  });

  return (
    <div style={{ maxWidth: '1000px', display: 'flex', flexDirection: 'column', gap: '24px' }}>
      <div>
        <h2 style={{ margin: 0 }}>Platform Admin</h2>
        <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Operate the whole platform — stores, users, plans and access</span>
      </div>

      <div style={{ borderBottom: '1px solid var(--color-border)', display: 'flex', gap: '2px', overflowX: 'auto' }}>
        {TABS.map((t) => (
          <button key={t.key} style={tabStyle(tab === t.key)} onClick={() => setTab(t.key)}>{t.label}</button>
        ))}
      </div>

      {tab === 'overview' && <OverviewTab />}
      {tab === 'stores' && <StoresTab />}
      {tab === 'users' && <UsersTab />}
      {tab === 'plans' && <PlansTab />}
      {tab === 'audit' && <AuditTab />}
    </div>
  );
};
