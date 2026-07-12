import React, { useCallback, useEffect, useState } from 'react';
import { campaignsApi, segmentsApi, messagingApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { TextInput } from '../../../components/forms/TextInput';
import { SelectInput } from '../../../components/forms/SelectInput';
import { DataTable, Column } from '../../../components/tables/DataTable';
import { StatusPill } from '../../../components/feedback/StatusPill';
import { AppIcon } from '../../../components/icons/AppIcon';
import { useToastStore } from '../../../stores/toastStore';
import { StatCard, TabIntro, ConfirmButton, apiErrorMessage } from '../marketingShared';

interface CampaignRow {
  id: number;
  subject: string;
  status: 'draft' | 'scheduled' | 'sent';
  channel?: 'email' | 'whatsapp';
  wa_template_name?: string | null;
  audience: { type: string; segment_id?: number };
  scheduled_at: string | null;
  sent_count: number;
  failed_count: number;
}

interface SegmentOption { id: number; name: string; count: number; }

interface ChannelStatus {
  email: { ready: boolean; mode: string };
  whatsapp: { ready: boolean; display_number: string | null };
}

export const CampaignsTab: React.FC<{
  isAdmin: boolean;
  hasSegments: boolean;
  initialSegmentId?: number;
  onOpenChannels: () => void;
}> = ({ isAdmin, hasSegments, initialSegmentId, onOpenChannels }) => {
  const { addToast } = useToastStore();
  const [campaigns, setCampaigns] = useState<CampaignRow[]>([]);
  const [segments, setSegments] = useState<SegmentOption[]>([]);
  const [channels, setChannels] = useState<ChannelStatus | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [showForm, setShowForm] = useState(Boolean(initialSegmentId));
  const [form, setForm] = useState({
    subject: '', body: '', channel: '',
    wa_template_name: '', wa_template_language: 'en',
    audience_type: initialSegmentId ? 'segment' : 'all',
    segment_id: initialSegmentId ? String(initialSegmentId) : '',
    scheduled_at: '',
  });

  const load = useCallback(async () => {
    try { setCampaigns(((await campaignsApi.list()) as any).campaigns ?? []); } catch { /* locked */ }
    try { setChannels(((await messagingApi.get()) as any).status ?? null); } catch { /* not fatal */ }
    if (hasSegments) {
      try { setSegments(((await segmentsApi.list()) as any).segments ?? []); } catch { /* locked */ }
    }
    setLoading(false);
  }, [hasSegments]);

  useEffect(() => { load(); }, [load]);

  // Default the channel to the first ready one, once status arrives.
  useEffect(() => {
    if (channels && !form.channel) {
      setForm(f => ({ ...f, channel: channels.email.ready ? 'email' : channels.whatsapp.ready ? 'whatsapp' : '' }));
    }
  }, [channels]); // eslint-disable-line react-hooks/exhaustive-deps

  const anyChannelReady = Boolean(channels && (channels.email.ready || channels.whatsapp.ready));

  const submit = async (sendNow: boolean) => {
    if (!form.channel) { addToast('Set up a channel first.', 'warning'); return; }
    if (!form.subject.trim() || !form.body.trim()) { addToast('Subject and message are required.', 'warning'); return; }
    if (form.audience_type === 'segment' && !form.segment_id) { addToast('Pick a segment for this audience.', 'warning'); return; }
    setSaving(true);
    try {
      const created = (await campaignsApi.create({
        subject: form.subject, body: form.body, channel: form.channel,
        wa_template_name: form.channel === 'whatsapp' ? (form.wa_template_name || null) : null,
        wa_template_language: form.channel === 'whatsapp' ? (form.wa_template_language || 'en') : null,
        audience: form.audience_type === 'segment' ? { type: 'segment', segment_id: Number(form.segment_id) } : { type: 'all' },
        scheduled_at: sendNow ? null : (form.scheduled_at || null),
      })) as any;
      if (sendNow) {
        const res = (await campaignsApi.sendNow(created.id)) as any;
        addToast(`Campaign sent to ${res.sent_count} customer${res.sent_count === 1 ? '' : 's'}.`, 'success');
      } else {
        addToast(form.scheduled_at ? 'Campaign scheduled.' : 'Campaign saved as draft.', 'success');
      }
      setForm({ subject: '', body: '', channel: form.channel, wa_template_name: '', wa_template_language: 'en', audience_type: 'all', segment_id: '', scheduled_at: '' });
      setShowForm(false);
      await load();
    } catch (e) { addToast(apiErrorMessage(e), 'danger'); } finally { setSaving(false); }
  };

  const sendNow = async (c: CampaignRow) => {
    try {
      const res = (await campaignsApi.sendNow(c.id)) as any;
      addToast(`"${c.subject}" sent to ${res.sent_count} customer${res.sent_count === 1 ? '' : 's'}.`, 'success');
      await load();
    } catch (e) { addToast(apiErrorMessage(e), 'danger'); }
  };

  const remove = async (c: CampaignRow) => {
    try { await campaignsApi.remove(c.id); addToast('Campaign deleted.', 'success'); await load(); } catch (e) { addToast(apiErrorMessage(e), 'danger'); }
  };

  const audienceLabel = (c: CampaignRow) => c.audience?.type === 'segment'
    ? (segments.find(s => s.id === c.audience.segment_id)?.name ?? 'Segment')
    : 'All customers';

  const totalRecipients = campaigns.reduce((s, c) => s + c.sent_count, 0);

  const channelOptions = channels ? [
    { value: 'email', label: channels.email.ready ? 'Email' : 'Email — not set up', disabled: !channels.email.ready },
    { value: 'whatsapp', label: channels.whatsapp.ready ? `WhatsApp${channels.whatsapp.display_number ? ` (${channels.whatsapp.display_number})` : ''}` : 'WhatsApp — not connected', disabled: !channels.whatsapp.ready },
  ] : [];

  const columns: Column<CampaignRow>[] = [
    { key: 'subject', header: 'Campaign', render: c => <b>{c.subject}</b> },
    { key: 'channel', header: 'Channel', render: c => (
      <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
        <AppIcon name={c.channel === 'whatsapp' ? 'whatsapp' : 'mail'} size={15} />
        {c.channel === 'whatsapp' ? (c.wa_template_name ? `WhatsApp · ${c.wa_template_name}` : 'WhatsApp') : 'Email'}
      </span>
    ) },
    { key: 'audience', header: 'Audience', render: c => audienceLabel(c) },
    { key: 'status', header: 'Status', render: c => <StatusPill status={c.status === 'sent' ? 'COMPLETE' : c.status === 'scheduled' ? 'CONFIRMING' : 'PENDING'} label={c.status[0].toUpperCase() + c.status.slice(1)} /> },
    { key: 'when', header: 'When', render: c => <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{c.scheduled_at ? new Date(c.scheduled_at).toLocaleString() : '—'}</span> },
    { key: 'sent', header: 'Delivered', render: c => c.status === 'sent'
      ? <span>{c.sent_count}{c.failed_count > 0 && <span style={{ color: 'var(--dorzak-danger)', fontSize: '0.8rem' }}> · {c.failed_count} failed</span>}</span>
      : <span>—</span> },
    ...(isAdmin ? [{
      key: 'actions', header: '', render: (c: CampaignRow) => (
        <span style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }} onClick={e => e.stopPropagation()}>
          {c.status !== 'sent' && <AppButton variant="secondary" onClick={() => sendNow(c)}>Send now</AppButton>}
          {c.status !== 'sent' && <ConfirmButton onConfirm={() => remove(c)} />}
        </span>
      ),
    }] : []),
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro title="Campaigns" sub="Reach your customers by email or WhatsApp — everyone, or a saved segment.">
        {isAdmin && anyChannelReady && <AppButton variant="primary" icon="plus" onClick={() => setShowForm(v => !v)}>{showForm ? 'Close' : 'New campaign'}</AppButton>}
      </TabIntro>

      {channels && !anyChannelReady && (
        <div className="card" style={{ padding: 18, display: 'flex', alignItems: 'center', gap: 12, justifyContent: 'space-between', flexWrap: 'wrap' }}>
          <span style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: '0.9rem' }}>
            <AppIcon name="alert" size={16} /> No sending channel is set up yet — campaigns can't go out until email or WhatsApp is configured.
          </span>
          {isAdmin && <AppButton variant="primary" onClick={onOpenChannels}>Set up channels</AppButton>}
        </div>
      )}

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12 }}>
        <StatCard label="Campaigns sent" value={campaigns.filter(c => c.status === 'sent').length} tone="success" />
        <StatCard label="Scheduled" value={campaigns.filter(c => c.status === 'scheduled').length} tone="muted" />
        <StatCard label="Messages delivered" value={totalRecipients} />
      </div>

      {showForm && (
        <div className="card" style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 14 }}>
          <TextInput label="Subject" value={form.subject} onChange={e => setForm({ ...form, subject: e.target.value })} placeholder="Weekend sale — 20% off everything" />
          <label className="form-label" style={{ display: 'block' }}>Message
            <textarea value={form.body} onChange={e => setForm({ ...form, body: e.target.value })} rows={4}
              style={{ width: '100%', marginTop: 6, padding: 10, borderRadius: 8, border: '1px solid var(--color-border)', font: 'inherit', resize: 'vertical' }}
              placeholder="Write the message your customers will receive…" />
          </label>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: 12 }}>
            <SelectInput label="Channel" value={form.channel} onChange={e => setForm({ ...form, channel: e.target.value })}
              options={channelOptions} />
            <SelectInput label="Audience" value={form.audience_type} onChange={e => setForm({ ...form, audience_type: e.target.value })}
              options={[{ value: 'all', label: 'All customers' }, ...(hasSegments && segments.length ? [{ value: 'segment', label: 'A saved segment' }] : [])]} />
            {form.audience_type === 'segment' && (
              <SelectInput label="Segment" value={form.segment_id} onChange={e => setForm({ ...form, segment_id: e.target.value })}
                options={[{ value: '', label: 'Choose…' }, ...segments.map(s => ({ value: String(s.id), label: `${s.name} (${s.count})` }))]} />
            )}
            <TextInput label="Schedule for (optional)" type="datetime-local" value={form.scheduled_at} onChange={e => setForm({ ...form, scheduled_at: e.target.value })} />
          </div>
          {form.channel === 'whatsapp' && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
              <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 12 }}>
                <TextInput label="Approved template name" value={form.wa_template_name} onChange={e => setForm({ ...form, wa_template_name: e.target.value })} placeholder="weekend_sale" />
                <TextInput label="Template language" value={form.wa_template_language} onChange={e => setForm({ ...form, wa_template_language: e.target.value })} placeholder="en" />
              </div>
              <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)', display: 'flex', gap: 6, alignItems: 'center' }}>
                <AppIcon name="info" size={14} /> Meta requires an approved template for marketing messages outside a 24-hour customer session. Leave blank only if you're replying within active sessions — the message text above is used instead.
              </p>
            </div>
          )}
          <div style={{ display: 'flex', gap: 8 }}>
            <AppButton variant="primary" loading={saving} onClick={() => submit(true)}>Send now</AppButton>
            <AppButton variant="secondary" loading={saving} onClick={() => submit(false)}>{form.scheduled_at ? 'Schedule' : 'Save draft'}</AppButton>
          </div>
        </div>
      )}

      {loading ? (
        <div style={{ color: 'var(--text-muted)', padding: 30, textAlign: 'center' }}>Loading campaigns…</div>
      ) : campaigns.length === 0 ? (
        <div className="card" style={{ padding: 30, textAlign: 'center', color: 'var(--text-muted)' }}>
          No campaigns yet. Announce a sale, a new product, or a slow-day offer — it reaches every customer with contact details on file.
        </div>
      ) : (
        <DataTable columns={columns} data={campaigns} keyExtractor={c => String(c.id)} selectable={false} />
      )}
    </div>
  );
};
