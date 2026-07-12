import React, { useEffect, useState } from 'react';
import { loyaltyApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { TextInput } from '../../../components/forms/TextInput';
import { ToggleSwitch } from '../../../components/forms/ToggleSwitch';
import { useToastStore } from '../../../stores/toastStore';
import { useMoney } from '../../../hooks/useMoney';
import { StatCard, TabIntro, apiErrorMessage } from '../marketingShared';

export const LoyaltyTab: React.FC<{ isAdmin: boolean }> = ({ isAdmin }) => {
  const { addToast } = useToastStore();
  const money = useMoney();
  const [saving, setSaving] = useState(false);
  const [enabled, setEnabled] = useState(true);
  const [earnRate, setEarnRate] = useState('1');
  const [redeemPoints, setRedeemPoints] = useState('100');
  const [redeemValue, setRedeemValue] = useState('5');
  const [stats, setStats] = useState({ members: 0, points_outstanding: 0 });

  useEffect(() => {
    (async () => {
      try {
        const res = (await loyaltyApi.get()) as any;
        if (res.loyalty) {
          setEnabled(res.loyalty.enabled);
          setEarnRate(String(res.loyalty.earn_points_per_currency));
          setRedeemPoints(String(res.loyalty.redeem_points));
          setRedeemValue(String(res.loyalty.redeem_value));
        }
        if (res.stats) setStats(res.stats);
      } catch { /* locked */ }
    })();
  }, []);

  const save = async () => {
    setSaving(true);
    try {
      await loyaltyApi.update({
        enabled,
        earn_points_per_currency: Number(earnRate) || 0,
        redeem_points: Number(redeemPoints) || 1,
        redeem_value: Number(redeemValue) || 0,
      });
      addToast('Loyalty program saved.', 'success');
    } catch (e) { addToast(apiErrorMessage(e), 'danger'); } finally { setSaving(false); }
  };

  const liability = (stats.points_outstanding / Math.max(1, Number(redeemPoints) || 1)) * (Number(redeemValue) || 0);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro title="Loyalty program" sub="Customers earn points on every completed order and spend them as a discount at checkout." />

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12 }}>
        <StatCard label="Members with points" value={stats.members} tone="success" />
        <StatCard label="Points outstanding" value={stats.points_outstanding.toLocaleString()} />
        <StatCard label="Redemption liability" value={money(liability)} tone="muted" hint="if every point were spent today" />
      </div>

      <div className="card" style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 16, maxWidth: 560 }}>
        <ToggleSwitch checked={enabled} onChange={setEnabled} label="Loyalty program active"
          description="Points accrue automatically on completed orders for identified customers." />
        <TextInput label="Points earned per 1 spent" type="number" min="0" value={earnRate} onChange={e => setEarnRate(e.target.value)} />
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
          <TextInput label="Points to redeem" type="number" min="1" value={redeemPoints} onChange={e => setRedeemPoints(e.target.value)} />
          <TextInput label="…worth this discount" type="number" min="0" value={redeemValue} onChange={e => setRedeemValue(e.target.value)} />
        </div>
        <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>
          Example with these settings: a customer spending {money(100)} earns {(Number(earnRate) || 0) * 100} points; {redeemPoints} points take {money(Number(redeemValue) || 0)} off a future order.
        </p>
        {isAdmin && <div><AppButton variant="primary" loading={saving} onClick={save}>Save changes</AppButton></div>}
      </div>
    </div>
  );
};
