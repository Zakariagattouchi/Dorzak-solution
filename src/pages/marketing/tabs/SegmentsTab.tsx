import React, { useCallback, useEffect, useState } from 'react';
import { segmentsApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { TextInput } from '../../../components/forms/TextInput';
import { DataTable, Column } from '../../../components/tables/DataTable';
import { useToastStore } from '../../../stores/toastStore';
import { useMoney } from '../../../hooks/useMoney';
import { TabIntro, ConfirmButton, apiErrorMessage } from '../marketingShared';

interface SegmentRow {
  id: number;
  name: string;
  rules: { min_orders?: number; max_orders?: number; min_spent?: number; max_spent?: number };
  count: number;
}

export const SegmentsTab: React.FC<{
  isAdmin: boolean;
  onCampaignTo: (segmentId: number) => void;
}> = ({ isAdmin, onCampaignTo }) => {
  const { addToast } = useToastStore();
  const money = useMoney();
  const [segments, setSegments] = useState<SegmentRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({
    name: '',
    min_orders: '',
    max_orders: '',
    min_spent: '',
    max_spent: '',
  });

  const load = useCallback(async () => {
    try {
      setSegments(((await segmentsApi.list()) as any).segments ?? []);
    } catch {
      /* locked */
    }
    setLoading(false);
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const create = async () => {
    if (!form.name.trim()) {
      addToast('Give the segment a name.', 'warning');
      return;
    }
    setSaving(true);
    try {
      const rules: Record<string, number> = {};
      if (form.min_orders) rules.min_orders = Number(form.min_orders);
      if (form.max_orders) rules.max_orders = Number(form.max_orders);
      if (form.min_spent) rules.min_spent = Number(form.min_spent);
      if (form.max_spent) rules.max_spent = Number(form.max_spent);
      await segmentsApi.create({ name: form.name.trim(), rules });
      setForm({ name: '', min_orders: '', max_orders: '', min_spent: '', max_spent: '' });
      setShowForm(false);
      addToast('Segment saved.', 'success');
      await load();
    } catch (e) {
      addToast(apiErrorMessage(e), 'danger');
    } finally {
      setSaving(false);
    }
  };

  const remove = async (s: SegmentRow) => {
    try {
      await segmentsApi.remove(s.id);
      addToast(`Segment "${s.name}" deleted.`, 'success');
      await load();
    } catch (e) {
      addToast(apiErrorMessage(e), 'danger');
    }
  };

  const rulesLabel = (s: SegmentRow) => {
    const parts: string[] = [];
    if (s.rules?.min_orders != null) parts.push(`≥ ${s.rules.min_orders} orders`);
    if (s.rules?.max_orders != null) parts.push(`≤ ${s.rules.max_orders} orders`);
    if (s.rules?.min_spent != null) parts.push(`spent ≥ ${money(s.rules.min_spent)}`);
    if (s.rules?.max_spent != null) parts.push(`spent ≤ ${money(s.rules.max_spent)}`);
    return parts.length ? parts.join(' · ') : 'Everyone';
  };

  const columns: Column<SegmentRow>[] = [
    { key: 'name', header: 'Segment', render: (s) => <b>{s.name}</b> },
    {
      key: 'rules',
      header: 'Rules',
      render: (s) => (
        <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>{rulesLabel(s)}</span>
      ),
    },
    { key: 'count', header: 'Customers', render: (s) => String(s.count) },
    ...(isAdmin
      ? [
          {
            key: 'actions',
            header: '',
            render: (s: SegmentRow) => (
              <span
                style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }}
                onClick={(e) => e.stopPropagation()}
              >
                <AppButton variant="secondary" onClick={() => onCampaignTo(s.id)}>
                  Campaign to this
                </AppButton>
                <ConfirmButton onConfirm={() => remove(s)} />
              </span>
            ),
          },
        ]
      : []),
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro
        title="Customer segments"
        sub="Save rule-based groups — VIPs, one-timers, big spenders — and target campaigns at them."
      >
        {isAdmin && (
          <AppButton variant="primary" icon="plus" onClick={() => setShowForm((v) => !v)}>
            {showForm ? 'Close' : 'New segment'}
          </AppButton>
        )}
      </TabIntro>

      {showForm && (
        <div
          className="card"
          style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 14 }}
        >
          <TextInput
            label="Name"
            value={form.name}
            onChange={(e) => setForm({ ...form, name: e.target.value })}
            placeholder="VIP customers"
          />
          <div
            style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
              gap: 12,
            }}
          >
            <TextInput
              label="Min orders"
              type="number"
              min="0"
              value={form.min_orders}
              onChange={(e) => setForm({ ...form, min_orders: e.target.value })}
            />
            <TextInput
              label="Max orders"
              type="number"
              min="0"
              value={form.max_orders}
              onChange={(e) => setForm({ ...form, max_orders: e.target.value })}
            />
            <TextInput
              label="Min total spent"
              type="number"
              min="0"
              value={form.min_spent}
              onChange={(e) => setForm({ ...form, min_spent: e.target.value })}
            />
            <TextInput
              label="Max total spent"
              type="number"
              min="0"
              value={form.max_spent}
              onChange={(e) => setForm({ ...form, max_spent: e.target.value })}
            />
          </div>
          <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>
            Leave a field blank to skip that rule. A customer must match every rule you set.
          </p>
          <div>
            <AppButton variant="primary" loading={saving} onClick={create}>
              Save segment
            </AppButton>
          </div>
        </div>
      )}

      {loading ? (
        <div style={{ color: 'var(--text-muted)', padding: 30, textAlign: 'center' }}>
          Loading segments…
        </div>
      ) : segments.length === 0 ? (
        <div
          className="card"
          style={{ padding: 30, textAlign: 'center', color: 'var(--text-muted)' }}
        >
          No segments yet. Try "VIPs — spent over 500" or "One-time buyers — exactly 1 order" and
          send them a tailored campaign.
        </div>
      ) : (
        <DataTable
          columns={columns}
          data={segments}
          keyExtractor={(s) => String(s.id)}
          selectable={false}
        />
      )}
    </div>
  );
};
