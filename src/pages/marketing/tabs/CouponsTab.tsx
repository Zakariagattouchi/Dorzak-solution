import React, { useCallback, useEffect, useState } from 'react';
import { couponsApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { TextInput } from '../../../components/forms/TextInput';
import { SelectInput } from '../../../components/forms/SelectInput';
import { DataTable, Column } from '../../../components/tables/DataTable';
import { StatusPill } from '../../../components/feedback/StatusPill';
import { useToastStore } from '../../../stores/toastStore';
import { useMoney } from '../../../hooks/useMoney';
import { StatCard, TabIntro, ConfirmButton, apiErrorMessage } from '../marketingShared';

export interface CouponRow {
  id: number;
  code: string;
  type: 'PERCENT' | 'FIXED';
  value: number;
  min_order: number;
  max_discount: number | null;
  usage_limit: number | null;
  used_count: number;
  active: boolean;
  expires_at: string | null;
  orders: number;
  revenue: number;
  discount_given: number;
}

const couponStatus = (c: CouponRow): { status: string; label: string } => {
  if (!c.active) return { status: 'INACTIVE', label: 'Off' };
  if (c.expires_at && new Date(c.expires_at) < new Date()) return { status: 'CANCELLED', label: 'Expired' };
  if (c.usage_limit != null && c.used_count >= c.usage_limit) return { status: 'CANCELLED', label: 'Exhausted' };
  return { status: 'ACTIVE', label: 'Active' };
};

export const CouponsTab: React.FC<{ isAdmin: boolean }> = ({ isAdmin }) => {
  const { addToast } = useToastStore();
  const money = useMoney();
  const [coupons, setCoupons] = useState<CouponRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ code: '', type: 'PERCENT', value: '', min_order: '', max_discount: '', usage_limit: '', expires_at: '' });

  const load = useCallback(async () => {
    try { setCoupons(((await couponsApi.list()) as any).coupons ?? []); } catch { /* locked or empty */ }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  const create = async () => {
    if (!form.code.trim() || !form.value) { addToast('A code and a value are required.', 'warning'); return; }
    setSaving(true);
    try {
      await couponsApi.create({
        code: form.code.trim(), type: form.type, value: Number(form.value),
        min_order: form.min_order ? Number(form.min_order) : 0,
        max_discount: form.max_discount ? Number(form.max_discount) : null,
        usage_limit: form.usage_limit ? Number(form.usage_limit) : null,
        expires_at: form.expires_at || null,
      });
      setForm({ code: '', type: 'PERCENT', value: '', min_order: '', max_discount: '', usage_limit: '', expires_at: '' });
      setShowForm(false);
      addToast('Coupon created.', 'success');
      await load();
    } catch (e) { addToast(apiErrorMessage(e), 'danger'); } finally { setSaving(false); }
  };

  const toggle = async (c: CouponRow) => {
    try { await couponsApi.update(c.id, { active: !c.active }); await load(); } catch (e) { addToast(apiErrorMessage(e), 'danger'); }
  };

  const remove = async (c: CouponRow) => {
    try { await couponsApi.remove(c.id); addToast(`Coupon ${c.code} deleted.`, 'success'); await load(); } catch (e) { addToast(apiErrorMessage(e), 'danger'); }
  };

  const totalRevenue = coupons.reduce((s, c) => s + c.revenue, 0);
  const totalDiscount = coupons.reduce((s, c) => s + c.discount_given, 0);
  const totalRedemptions = coupons.reduce((s, c) => s + c.used_count, 0);

  const columns: Column<CouponRow>[] = [
    { key: 'code', header: 'Code', render: c => <span style={{ fontFamily: 'monospace', fontWeight: 700 }}>{c.code}</span> },
    { key: 'discount', header: 'Discount', render: c => c.type === 'PERCENT' ? `${c.value}%${c.max_discount ? ` (max ${money(c.max_discount)})` : ''}` : money(c.value) },
    { key: 'rules', header: 'Rules', render: c => <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{[c.min_order > 0 ? `min ${money(c.min_order)}` : null, c.expires_at ? `until ${c.expires_at}` : null].filter(Boolean).join(' · ') || '—'}</span> },
    { key: 'usage', header: 'Used', render: c => `${c.used_count}${c.usage_limit != null ? ` / ${c.usage_limit}` : ''}` },
    { key: 'orders', header: 'Orders', render: c => String(c.orders) },
    { key: 'revenue', header: 'Revenue driven', render: c => <b>{money(c.revenue)}</b> },
    { key: 'given', header: 'Discount given', render: c => <span style={{ color: 'var(--dorzak-danger)' }}>-{money(c.discount_given)}</span> },
    { key: 'status', header: 'Status', render: c => { const s = couponStatus(c); return <StatusPill status={s.status} label={s.label} />; } },
    ...(isAdmin ? [{
      key: 'actions', header: '', render: (c: CouponRow) => (
        <span style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }} onClick={e => e.stopPropagation()}>
          <AppButton variant="tertiary" onClick={() => toggle(c)}>{c.active ? 'Turn off' : 'Turn on'}</AppButton>
          <ConfirmButton onConfirm={() => remove(c)} />
        </span>
      ),
    }] : []),
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro title="Coupon codes" sub="Discount codes customers apply at checkout — tracked down to the revenue each code drives.">
        {isAdmin && <AppButton variant="primary" icon="plus" onClick={() => setShowForm(v => !v)}>{showForm ? 'Close' : 'New coupon'}</AppButton>}
      </TabIntro>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12 }}>
        <StatCard label="Revenue driven" value={money(totalRevenue)} tone="success" />
        <StatCard label="Discount given" value={`-${money(totalDiscount)}`} tone="danger" />
        <StatCard label="Redemptions" value={totalRedemptions} tone="muted" />
      </div>

      {showForm && (
        <div className="card" style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 14 }}>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12 }}>
            <TextInput label="Code" value={form.code} onChange={e => setForm({ ...form, code: e.target.value.toUpperCase() })} placeholder="WELCOME10" />
            <SelectInput label="Type" value={form.type} onChange={e => setForm({ ...form, type: e.target.value })} options={[{ value: 'PERCENT', label: 'Percent off' }, { value: 'FIXED', label: 'Fixed amount off' }]} />
            <TextInput label={form.type === 'PERCENT' ? 'Percent (%)' : 'Amount'} type="number" min="0" value={form.value} onChange={e => setForm({ ...form, value: e.target.value })} />
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12 }}>
            <TextInput label="Minimum order (optional)" type="number" min="0" value={form.min_order} onChange={e => setForm({ ...form, min_order: e.target.value })} />
            {form.type === 'PERCENT' && <TextInput label="Max discount (optional)" type="number" min="0" value={form.max_discount} onChange={e => setForm({ ...form, max_discount: e.target.value })} />}
            <TextInput label="Usage limit (optional)" type="number" min="1" value={form.usage_limit} onChange={e => setForm({ ...form, usage_limit: e.target.value })} />
            <TextInput label="Expires (optional)" type="date" value={form.expires_at} onChange={e => setForm({ ...form, expires_at: e.target.value })} />
          </div>
          <div><AppButton variant="primary" loading={saving} onClick={create}>Create coupon</AppButton></div>
        </div>
      )}

      {loading ? (
        <div style={{ color: 'var(--text-muted)', padding: 30, textAlign: 'center' }}>Loading coupons…</div>
      ) : coupons.length === 0 ? (
        <div className="card" style={{ padding: 30, textAlign: 'center', color: 'var(--text-muted)' }}>
          No coupons yet. Create your first code — customers enter it at checkout and you'll see the orders it drives here.
        </div>
      ) : (
        <DataTable columns={columns} data={coupons} keyExtractor={c => String(c.id)} selectable={false} />
      )}
    </div>
  );
};
