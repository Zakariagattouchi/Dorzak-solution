import React, { useState, useEffect } from 'react';
import { AppButton } from '../../components/buttons/AppButton';
import { TextInput } from '../../components/forms/TextInput';
import { SelectInput } from '../../components/forms/SelectInput';
import { ToggleSwitch } from '../../components/forms/ToggleSwitch';
import { AppIcon, IconName } from '../../components/icons/AppIcon';
import { useToastStore } from '../../stores/toastStore';
import { couponsApi, campaignsApi, referralApi, giftCardsApi, segmentsApi, reviewsApi } from '../../api/endpoints';

type Tab = 'COUPONS' | 'CAMPAIGNS' | 'REFERRALS' | 'GIFT_CARDS' | 'SEGMENTS' | 'REVIEWS';

const TABS: { key: Tab; label: string; icon: IconName }[] = [
  { key: 'COUPONS', label: 'Coupons', icon: 'tag' as IconName },
  { key: 'CAMPAIGNS', label: 'Campaigns', icon: 'send' as IconName },
  { key: 'REFERRALS', label: 'Referrals', icon: 'userPlus' },
  { key: 'GIFT_CARDS', label: 'Gift cards', icon: 'card' as IconName },
  { key: 'SEGMENTS', label: 'Segments', icon: 'customers' },
  { key: 'REVIEWS', label: 'Reviews', icon: 'star' },
];

export const MarketingPage: React.FC = () => {
  const { addToast } = useToastStore();
  const [tab, setTab] = useState<Tab>('COUPONS');

  // Coupons
  const [coupons, setCoupons] = useState<any[]>([]);
  const [couponForm, setCouponForm] = useState({ code: '', type: 'PERCENT', value: '' });
  // Campaigns
  const [campaigns, setCampaigns] = useState<any[]>([]);
  const [campaignForm, setCampaignForm] = useState({ subject: '', body: '', channel: 'email', audience_type: 'all', scheduled_at: '' });
  // Referrals
  const [referral, setReferral] = useState({ enabled: true, referrer_reward: '15', referee_reward: '5' });
  // Gift cards
  const [giftCards, setGiftCards] = useState<any[]>([]);
  const [giftAmount, setGiftAmount] = useState('');
  const [lastGift, setLastGift] = useState<string | null>(null);
  // Segments
  const [segments, setSegments] = useState<any[]>([]);
  const [segForm, setSegForm] = useState({ name: '', min_orders: '', min_spent: '' });
  // Reviews
  const [reviews, setReviews] = useState<any[]>([]);

  const fail = (e: any) => addToast(e?.data?.message || (e?.status === 402 ? 'Upgrade your plan to use this.' : 'Something went wrong.'), 'danger');

  useEffect(() => {
    (async () => {
      try {
        if (tab === 'COUPONS') setCoupons(((await couponsApi.list()) as any).coupons ?? []);
        if (tab === 'CAMPAIGNS') setCampaigns(((await campaignsApi.list()) as any).campaigns ?? []);
        if (tab === 'REFERRALS') { const d = ((await referralApi.get()) as any).referral; if (d) setReferral({ enabled: d.enabled, referrer_reward: String(d.referrer_reward), referee_reward: String(d.referee_reward) }); }
        if (tab === 'GIFT_CARDS') setGiftCards(((await giftCardsApi.list()) as any).gift_cards ?? []);
        if (tab === 'SEGMENTS') setSegments(((await segmentsApi.list()) as any).segments ?? []);
        if (tab === 'REVIEWS') setReviews(((await reviewsApi.list()) as any).reviews ?? []);
      } catch (e) { /* empty on 402/none */ }
    })();
  }, [tab]);

  const createCoupon = async () => {
    try { await couponsApi.create({ code: couponForm.code, type: couponForm.type, value: Number(couponForm.value) }); setCouponForm({ code: '', type: 'PERCENT', value: '' }); setCoupons(((await couponsApi.list()) as any).coupons ?? []); addToast('Coupon created.', 'success'); } catch (e) { fail(e); }
  };
  const createCampaign = async () => {
    try {
      await campaignsApi.create({ subject: campaignForm.subject, body: campaignForm.body, channel: campaignForm.channel, audience: { type: campaignForm.audience_type }, scheduled_at: campaignForm.scheduled_at || null });
      setCampaignForm({ subject: '', body: '', channel: 'email', audience_type: 'all', scheduled_at: '' });
      setCampaigns(((await campaignsApi.list()) as any).campaigns ?? []); addToast('Campaign saved.', 'success');
    } catch (e) { fail(e); }
  };
  const saveReferral = async () => {
    try { await referralApi.update({ enabled: referral.enabled, referrer_reward: Number(referral.referrer_reward), referee_reward: Number(referral.referee_reward) }); addToast('Referral program saved.', 'success'); } catch (e) { fail(e); }
  };
  const issueGift = async () => {
    try { const r = (await giftCardsApi.issue(Number(giftAmount))) as any; setLastGift(r.code); setGiftAmount(''); setGiftCards(((await giftCardsApi.list()) as any).gift_cards ?? []); addToast('Gift card issued.', 'success'); } catch (e) { fail(e); }
  };
  const createSegment = async () => {
    try {
      const rules: any = {}; if (segForm.min_orders) rules.min_orders = Number(segForm.min_orders); if (segForm.min_spent) rules.min_spent = Number(segForm.min_spent);
      await segmentsApi.create({ name: segForm.name, rules }); setSegForm({ name: '', min_orders: '', min_spent: '' }); setSegments(((await segmentsApi.list()) as any).segments ?? []); addToast('Segment saved.', 'success');
    } catch (e) { fail(e); }
  };
  const approveReview = async (id: number) => { try { await reviewsApi.approve(id); setReviews(((await reviewsApi.list()) as any).reviews ?? []); } catch (e) { fail(e); } };

  return (
    <div style={{ padding: '24px', maxWidth: 900, margin: '0 auto' }}>
      <h1 style={{ fontSize: '1.4rem', fontWeight: 700, marginBottom: 4 }}>Marketing</h1>
      <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: 20 }}>Grow repeat business — coupons, campaigns, referrals, gift cards, segments and reviews.</p>

      <nav style={{ display: 'flex', gap: 4, borderBottom: '1px solid var(--color-border)', marginBottom: 20, overflowX: 'auto' }}>
        {TABS.map(t => (
          <button key={t.key} onClick={() => setTab(t.key)} className="min-h-11" style={{ padding: '10px 14px', border: 'none', borderBottom: `2px solid ${tab === t.key ? 'var(--color-primary)' : 'transparent'}`, background: 'none', fontWeight: 600, color: tab === t.key ? 'var(--color-primary)' : 'var(--text-muted)', cursor: 'pointer', whiteSpace: 'nowrap' }}>{t.label}</button>
        ))}
      </nav>

      {tab === 'COUPONS' && (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <h3>Create a coupon</h3>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12 }}>
            <TextInput label="Code" value={couponForm.code} onChange={e => setCouponForm({ ...couponForm, code: e.target.value })} placeholder="WELCOME10" />
            <SelectInput label="Type" value={couponForm.type} onChange={e => setCouponForm({ ...couponForm, type: e.target.value })} options={[{ value: 'PERCENT', label: 'Percent %' }, { value: 'FIXED', label: 'Fixed amount' }]} />
            <TextInput label="Value" type="number" value={couponForm.value} onChange={e => setCouponForm({ ...couponForm, value: e.target.value })} />
          </div>
          <div><AppButton variant="primary" onClick={createCoupon}>Create coupon</AppButton></div>
          <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
            {coupons.map(c => <li key={c.id} style={{ display: 'flex', justifyContent: 'space-between', padding: '8px 0', borderTop: '1px solid var(--color-border)' }}><span><b>{c.code}</b> — {c.type === 'PERCENT' ? `${c.value}%` : c.value} · used {c.used_count}</span></li>)}
          </ul>
        </div>
      )}

      {tab === 'CAMPAIGNS' && (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <h3>New campaign</h3>
          <TextInput label="Subject" value={campaignForm.subject} onChange={e => setCampaignForm({ ...campaignForm, subject: e.target.value })} />
          <label style={{ fontSize: '0.85rem', fontWeight: 600 }}>Message
            <textarea value={campaignForm.body} onChange={e => setCampaignForm({ ...campaignForm, body: e.target.value })} rows={4} style={{ width: '100%', marginTop: 6, padding: 10, borderRadius: 8, border: '1px solid var(--color-border)' }} />
          </label>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12 }}>
            <SelectInput label="Channel" value={campaignForm.channel} onChange={e => setCampaignForm({ ...campaignForm, channel: e.target.value })} options={[{ value: 'email', label: 'Email' }, { value: 'whatsapp', label: 'WhatsApp' }]} />
            <SelectInput label="Audience" value={campaignForm.audience_type} onChange={e => setCampaignForm({ ...campaignForm, audience_type: e.target.value })} options={[{ value: 'all', label: 'All customers' }]} />
            <TextInput label="Send at" type="datetime-local" value={campaignForm.scheduled_at} onChange={e => setCampaignForm({ ...campaignForm, scheduled_at: e.target.value })} />
          </div>
          {campaignForm.channel === 'whatsapp' && <p style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>WhatsApp sending activates once your WhatsApp Business API credentials are configured.</p>}
          <div><AppButton variant="primary" onClick={createCampaign}>Schedule campaign</AppButton></div>
          <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
            {campaigns.map(c => <li key={c.id} style={{ padding: '8px 0', borderTop: '1px solid var(--color-border)' }}><b>{c.subject}</b> · {c.status} · sent {c.sent_count}</li>)}
          </ul>
        </div>
      )}

      {tab === 'REFERRALS' && (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <h3>Referral program</h3>
          <ToggleSwitch checked={referral.enabled} onChange={v => setReferral({ ...referral, enabled: v })} label="Program active" description="Reward customers who refer a friend, in store credit." />
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <TextInput label="Referrer reward" type="number" value={referral.referrer_reward} onChange={e => setReferral({ ...referral, referrer_reward: e.target.value })} />
            <TextInput label="New-customer reward" type="number" value={referral.referee_reward} onChange={e => setReferral({ ...referral, referee_reward: e.target.value })} />
          </div>
          <div><AppButton variant="primary" onClick={saveReferral}>Save</AppButton></div>
        </div>
      )}

      {tab === 'GIFT_CARDS' && (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <h3>Issue a gift card</h3>
          <div style={{ display: 'flex', gap: 12, alignItems: 'flex-end' }}>
            <TextInput label="Amount" type="number" value={giftAmount} onChange={e => setGiftAmount(e.target.value)} />
            <AppButton variant="primary" onClick={issueGift}>Issue</AppButton>
          </div>
          {lastGift && <p style={{ fontWeight: 600 }}>New card: <span style={{ fontFamily: 'monospace' }}>{lastGift}</span></p>}
          <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
            {giftCards.map(g => <li key={g.id} style={{ padding: '8px 0', borderTop: '1px solid var(--color-border)' }}><span style={{ fontFamily: 'monospace' }}>{g.code}</span> — {g.amount} ({g.status})</li>)}
          </ul>
        </div>
      )}

      {tab === 'SEGMENTS' && (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <h3>Saved segments</h3>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12 }}>
            <TextInput label="Name" value={segForm.name} onChange={e => setSegForm({ ...segForm, name: e.target.value })} />
            <TextInput label="Min orders" type="number" value={segForm.min_orders} onChange={e => setSegForm({ ...segForm, min_orders: e.target.value })} />
            <TextInput label="Min spent" type="number" value={segForm.min_spent} onChange={e => setSegForm({ ...segForm, min_spent: e.target.value })} />
          </div>
          <div><AppButton variant="primary" onClick={createSegment}>Save segment</AppButton></div>
          <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
            {segments.map(s => <li key={s.id} style={{ padding: '8px 0', borderTop: '1px solid var(--color-border)' }}>{s.name} — {s.count} customers</li>)}
          </ul>
        </div>
      )}

      {tab === 'REVIEWS' && (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <h3>Product reviews</h3>
          <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
            {reviews.length === 0 && <li style={{ color: 'var(--text-muted)' }}>No reviews yet.</li>}
            {reviews.map(r => (
              <li key={r.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '10px 0', borderTop: '1px solid var(--color-border)' }}>
                <span>{'★'.repeat(r.rating)} — {r.comment} <em style={{ color: 'var(--text-muted)' }}>{r.approved ? '' : '(pending)'}</em></span>
                {!r.approved && <AppButton variant="secondary" onClick={() => approveReview(r.id)}>Approve</AppButton>}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
};
