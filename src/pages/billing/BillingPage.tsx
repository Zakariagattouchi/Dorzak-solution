import React, { useCallback, useEffect, useState } from 'react';
import { subscriptionApi } from '../../api/endpoints';
import { useAuthStore } from '../../stores/authStore';
import { useToastStore } from '../../stores/toastStore';
import { AppIcon } from '../../components/icons/AppIcon';
import { AppButton } from '../../components/buttons/AppButton';
import { StatusPill } from '../../components/feedback/StatusPill';

interface PlanFeatureRow { feature: string; limit: number | null; }

interface CatalogPlan {
  id: number;
  code: string;
  name_en: string;
  description_en: string | null;
  price: number;
  billing_cycle: string;
  trial_days: number;
  is_default: boolean;
  features: PlanFeatureRow[];
}

interface SubscriptionData {
  plan: string;
  plan_name: string;
  status: string;
  price: string | number;
  billing_cycle: string;
  renews_at: string | null;
  trial_ends_at: string | null;
  trial_used: boolean;
  plan_is_default: boolean;
  features: PlanFeatureRow[];
  currency: string;
}

const featureLabel = (f: PlanFeatureRow): string => {
  const label = f.feature.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
  return f.limit != null ? `${label} (${f.limit})` : label;
};

export const BillingPage: React.FC = () => {
  const { addToast } = useToastStore();
  const { can } = useAuthStore();
  const [sub, setSub] = useState<SubscriptionData | null>(null);
  const [plans, setPlans] = useState<CatalogPlan[]>([]);
  const [loading, setLoading] = useState(true);
  const [startingTrial, setStartingTrial] = useState<number | null>(null);

  const canManage = can('billing.manage');

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const [subRes, plansRes] = await Promise.all([
        subscriptionApi.get() as any,
        subscriptionApi.plans() as any,
      ]);
      setSub(subRes.data);
      setPlans(plansRes.data ?? []);
    } catch {
      addToast('Failed to load subscription', 'danger');
    } finally {
      setLoading(false);
    }
  }, [addToast]);

  useEffect(() => { load(); }, [load]);

  const startTrial = async (plan: CatalogPlan) => {
    setStartingTrial(plan.id);
    try {
      await subscriptionApi.startTrial(plan.id);
      addToast(`Your ${plan.trial_days}-day ${plan.name_en} trial has started!`, 'success');
      load();
    } catch (e: any) {
      addToast(e?.message ?? 'Could not start trial', 'danger');
    } finally {
      setStartingTrial(null);
    }
  };

  if (loading) {
    return <div style={{ color: 'var(--text-muted)', padding: '40px', textAlign: 'center' }}>Loading your plan…</div>;
  }

  const trialActive = sub?.status === 'TRIALING';
  const trialDaysLeft = sub?.trial_ends_at
    ? Math.max(0, Math.ceil((new Date(sub.trial_ends_at).getTime() - Date.now()) / 86_400_000))
    : 0;

  return (
    <div style={{ maxWidth: '1000px', display: 'flex', flexDirection: 'column', gap: '24px' }}>
      <div>
        <h2 style={{ margin: 0 }}>Plans & Subscription</h2>
        <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Manage your Dorzak Merchant plan and feature modules</span>
      </div>

      {/* Current plan */}
      {sub && (
        <div className="card" style={{ borderLeft: '4px solid var(--dorzak-primary)' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' }}>
            <div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '4px' }}>
                <h3 style={{ margin: 0, fontSize: '1.2rem' }}>{sub.plan_name ?? sub.plan}</h3>
                <StatusPill status={trialActive ? 'CONFIRMING' : 'ACTIVE'} label={sub.status} />
              </div>
              <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
                {trialActive
                  ? `Trial — ${trialDaysLeft} day${trialDaysLeft === 1 ? '' : 's'} left, then back to Free unless you upgrade`
                  : sub.plan_is_default
                    ? 'Free forever — upgrade any time to unlock your branded storefront and online ordering'
                    : sub.renews_at
                      ? `Renews ${new Date(sub.renews_at).toLocaleDateString()}`
                      : `${sub.price} ${sub.currency} / ${sub.billing_cycle}`}
              </span>
            </div>
          </div>

          {sub.features.length > 0 && (
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '14px' }}>
              {sub.features.map((f, i) => (
                <span key={i} style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '0.8rem', padding: '4px 10px', borderRadius: '14px', background: 'var(--color-bg)', fontWeight: 500 }}>
                  <AppIcon name="checkCircle" size={14} color="var(--dorzak-success)" />
                  {featureLabel(f)}
                </span>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Plan catalog */}
      <div style={{ display: 'grid', gridTemplateColumns: `repeat(${Math.min(plans.length, 3)}, 1fr)`, gap: '16px' }}>
        {plans.map((plan) => {
          const isCurrent = sub?.plan === plan.code;
          const canTrial = canManage && !plan.is_default && plan.trial_days > 0 && !sub?.trial_used && sub?.plan_is_default;

          return (
            <div key={plan.id} className="card" style={{ display: 'flex', flexDirection: 'column', gap: '12px', border: isCurrent ? '2px solid var(--dorzak-primary)' : undefined }}>
              <div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <h4 style={{ margin: 0, fontSize: '1.05rem' }}>{plan.name_en}</h4>
                  {isCurrent && <span style={{ fontSize: '0.7rem', fontWeight: 700, color: 'var(--dorzak-primary)' }}>CURRENT</span>}
                </div>
                {plan.description_en && (
                  <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{plan.description_en}</span>
                )}
              </div>

              <div style={{ fontSize: '1.6rem', fontWeight: 800 }}>
                {plan.price === 0 ? 'Free' : `${plan.price} ${sub?.currency ?? 'QAR'}`}
                {plan.price > 0 && <span style={{ fontSize: '0.8rem', fontWeight: 400, color: 'var(--text-muted)' }}> / {plan.billing_cycle}</span>}
              </div>

              <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: '6px', fontSize: '0.85rem', flex: 1, padding: 0, margin: 0 }}>
                {plan.features.map((f, i) => (
                  <li key={i} style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <AppIcon name="check" size={14} color="var(--dorzak-success)" />
                    {featureLabel(f)}
                  </li>
                ))}
              </ul>

              {!isCurrent && !plan.is_default && (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                  {canTrial && (
                    <AppButton
                      variant="primary"
                      loading={startingTrial === plan.id}
                      onClick={() => startTrial(plan)}
                    >
                      Start {plan.trial_days}-day free trial
                    </AppButton>
                  )}
                  <AppButton
                    variant={canTrial ? 'secondary' : 'primary'}
                    onClick={() => addToast('Online payments are launching soon — contact us to upgrade today', 'info')}
                  >
                    Upgrade to {plan.name_en}
                  </AppButton>
                </div>
              )}
            </div>
          );
        })}
      </div>

      <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>
        Free trials are one-time per store. When a trial ends your store returns to the Free plan automatically — your products, orders and customers are never touched.
      </span>
    </div>
  );
};
