import React, { useEffect, useState } from 'react';
import { referralApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { TextInput } from '../../../components/forms/TextInput';
import { ToggleSwitch } from '../../../components/forms/ToggleSwitch';
import { useToastStore } from '../../../stores/toastStore';
import { useMoney } from '../../../hooks/useMoney';
import { StatCard, TabIntro, apiErrorMessage } from '../marketingShared';

export const ReferralsTab: React.FC<{ isAdmin: boolean }> = ({ isAdmin }) => {
  const { addToast } = useToastStore();
  const money = useMoney();
  const [saving, setSaving] = useState(false);
  const [enabled, setEnabled] = useState(true);
  const [referrerReward, setReferrerReward] = useState('15');
  const [refereeReward, setRefereeReward] = useState('5');
  const [stats, setStats] = useState({ rewarded: 0, pending: 0, credit_issued: 0 });

  useEffect(() => {
    (async () => {
      try {
        const res = (await referralApi.get()) as any;
        if (res.referral) {
          setEnabled(res.referral.enabled);
          setReferrerReward(String(res.referral.referrer_reward));
          setRefereeReward(String(res.referral.referee_reward));
        }
        if (res.stats) setStats(res.stats);
      } catch { /* locked */ }
    })();
  }, []);

  const save = async () => {
    setSaving(true);
    try {
      await referralApi.update({ enabled, referrer_reward: Number(referrerReward) || 0, referee_reward: Number(refereeReward) || 0 });
      addToast('Referral program saved.', 'success');
    } catch (e) { addToast(apiErrorMessage(e), 'danger'); } finally { setSaving(false); }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro title="Referral program" sub="Customers share a personal code; when a friend places their first order, both earn store credit." />

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12 }}>
        <StatCard label="Friends referred" value={stats.rewarded} tone="success" hint="first orders completed" />
        <StatCard label="Pending" value={stats.pending} tone="muted" hint="referred, not yet ordered" />
        <StatCard label="Credit issued" value={money(stats.credit_issued)} hint="total rewards paid out" />
      </div>

      <div className="card" style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 16, maxWidth: 560 }}>
        <ToggleSwitch checked={enabled} onChange={setEnabled} label="Referral program active"
          description="Each customer's code appears on their receipts and order-status page." />
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
          <TextInput label="Reward for the referrer" type="number" min="0" value={referrerReward} onChange={e => setReferrerReward(e.target.value)} />
          <TextInput label="Welcome reward for the friend" type="number" min="0" value={refereeReward} onChange={e => setRefereeReward(e.target.value)} />
        </div>
        <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>
          Rewards are paid as store credit the moment the friend's first order completes. Self-referrals and repeat referrals are blocked automatically.
        </p>
        {isAdmin && <div><AppButton variant="primary" loading={saving} onClick={save}>Save changes</AppButton></div>}
      </div>
    </div>
  );
};
