import React, { useEffect, useMemo, useState } from 'react';
import { marketingApi, subscriptionApi } from '../../api/endpoints';
import { useAuthStore } from '../../stores/authStore';
import { useMoney } from '../../hooks/useMoney';
import { AppIcon, IconName } from '../../components/icons/AppIcon';
import { StatCard, LockedFeature } from './marketingShared';
import { CouponsTab } from './tabs/CouponsTab';
import { CampaignsTab } from './tabs/CampaignsTab';
import { ChannelsTab } from './tabs/ChannelsTab';
import { ReferralsTab } from './tabs/ReferralsTab';
import { GiftCardsTab } from './tabs/GiftCardsTab';
import { SegmentsTab } from './tabs/SegmentsTab';
import { ReviewsTab } from './tabs/ReviewsTab';
import { LoyaltyTab } from './tabs/LoyaltyTab';

type Tab =
  | 'COUPONS'
  | 'CAMPAIGNS'
  | 'CHANNELS'
  | 'LOYALTY'
  | 'REFERRALS'
  | 'GIFT_CARDS'
  | 'SEGMENTS'
  | 'REVIEWS';

/** Tab → the PlanFeature that unlocks it (mirrors the server's PlanGate). */
const TAB_FEATURE: Record<Tab, string> = {
  COUPONS: 'COUPONS',
  CAMPAIGNS: 'CAMPAIGNS',
  CHANNELS: 'CAMPAIGNS',
  LOYALTY: 'LOYALTY',
  REFERRALS: 'REFERRALS',
  GIFT_CARDS: 'GIFT_CARDS',
  SEGMENTS: 'SEGMENTS',
  REVIEWS: 'REVIEWS',
};

const TAB_META: { key: Tab; label: string; icon: IconName; lockedCopy: string }[] = [
  {
    key: 'COUPONS',
    label: 'Coupons',
    icon: 'tag',
    lockedCopy:
      'Create discount codes customers apply at checkout, and see the orders and revenue each code drives.',
  },
  {
    key: 'CAMPAIGNS',
    label: 'Campaigns',
    icon: 'mail',
    lockedCopy:
      'Send email or WhatsApp announcements to all customers or a saved segment — scheduled or immediately.',
  },
  {
    key: 'CHANNELS',
    label: 'Channels',
    icon: 'link',
    lockedCopy:
      'Connect your email sender and WhatsApp Business API so campaigns can actually deliver.',
  },
  {
    key: 'LOYALTY',
    label: 'Loyalty',
    icon: 'star',
    lockedCopy:
      'Reward repeat customers with points on every order, redeemable as a discount at checkout.',
  },
  {
    key: 'REFERRALS',
    label: 'Referrals',
    icon: 'userPlus',
    lockedCopy:
      'Give every customer a share code; both sides earn store credit when a friend places a first order.',
  },
  {
    key: 'GIFT_CARDS',
    label: 'Gift cards',
    icon: 'card',
    lockedCopy: 'Sell prepaid gift cards redeemable as store credit at checkout.',
  },
  {
    key: 'SEGMENTS',
    label: 'Segments',
    icon: 'customers',
    lockedCopy:
      'Save rule-based customer groups — VIPs, one-timers — and target campaigns at them.',
  },
  {
    key: 'REVIEWS',
    label: 'Reviews',
    icon: 'sparkles',
    lockedCopy: 'Collect product reviews and moderate them before they appear on your storefront.',
  },
];

interface Overview {
  coupons: { active: number; redemptions: number; revenue: number; discount_given: number };
  campaigns: { sent: number; scheduled: number; recipients: number };
  referrals: { rewarded: number; pending: number };
  gift_cards: { outstanding_value: number; redeemed: number };
  loyalty: { members: number; points_outstanding: number };
  store_credit: { outstanding: number };
  reviews: { pending: number; approved: number; average: number };
}

export const MarketingPage: React.FC = () => {
  const can = useAuthStore((s) => s.can);
  const isAdmin = can('settings.manage');
  const money = useMoney();

  const [tab, setTab] = useState<Tab>('COUPONS');
  const [unlocked, setUnlocked] = useState<Set<string> | null>(null);
  const [overview, setOverview] = useState<Overview | null>(null);
  const [campaignSegmentId, setCampaignSegmentId] = useState<number | undefined>(undefined);

  useEffect(() => {
    (async () => {
      try {
        const sub = (await subscriptionApi.get()) as any;
        const features = (sub.data?.features ?? sub.features ?? []) as { feature: string }[];
        setUnlocked(new Set(features.map((f) => f.feature)));
      } catch {
        setUnlocked(new Set());
      }
      try {
        setOverview((await marketingApi.overview()) as any);
      } catch {
        /* non-fatal */
      }
    })();
  }, []);

  const isUnlocked = useMemo(() => (t: Tab) => unlocked?.has(TAB_FEATURE[t]) ?? false, [unlocked]);

  const openCampaignForSegment = (segmentId: number) => {
    setCampaignSegmentId(segmentId);
    setTab('CAMPAIGNS');
  };

  const meta = TAB_META.find((t) => t.key === tab)!;

  return (
    <div
      style={{
        padding: '24px',
        maxWidth: 1040,
        margin: '0 auto',
        display: 'flex',
        flexDirection: 'column',
        gap: 18,
      }}
    >
      <div>
        <h2 style={{ margin: 0 }}>Marketing</h2>
        <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
          Everything that brings customers back — and what each program is actually producing
        </span>
      </div>

      {overview && (
        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fit, minmax(170px, 1fr))',
            gap: 12,
          }}
        >
          <StatCard
            label="Coupon revenue"
            value={money(overview.coupons.revenue)}
            tone="success"
            hint={`${overview.coupons.redemptions} redemptions`}
          />
          <StatCard
            label="Messages delivered"
            value={overview.campaigns.recipients}
            hint={`${overview.campaigns.sent} campaigns sent`}
          />
          <StatCard
            label="Loyalty points held"
            value={overview.loyalty.points_outstanding.toLocaleString()}
            hint={`${overview.loyalty.members} members`}
          />
          <StatCard
            label="Store credit outstanding"
            value={money(overview.store_credit.outstanding + overview.gift_cards.outstanding_value)}
            tone="muted"
            hint="wallets + unredeemed gift cards"
          />
          {overview.reviews.pending > 0 && (
            <StatCard label="Reviews to moderate" value={overview.reviews.pending} tone="danger" />
          )}
        </div>
      )}

      <nav
        style={{
          display: 'flex',
          gap: 2,
          borderBottom: '1px solid var(--color-border)',
          overflowX: 'auto',
        }}
        aria-label="Marketing sections"
      >
        {TAB_META.map((t) => {
          const locked = unlocked !== null && !isUnlocked(t.key);
          const active = tab === t.key;
          return (
            <button
              key={t.key}
              onClick={() => setTab(t.key)}
              style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: 6,
                padding: '10px 14px',
                minHeight: 44,
                border: 'none',
                background: 'none',
                cursor: 'pointer',
                whiteSpace: 'nowrap',
                fontWeight: 600,
                borderBottom: `2px solid ${active ? 'var(--dorzak-primary)' : 'transparent'}`,
                color: active
                  ? 'var(--dorzak-primary)'
                  : locked
                    ? 'var(--text-muted)'
                    : 'var(--color-text)',
                opacity: locked ? 0.75 : 1,
              }}
            >
              <AppIcon name={t.icon} size={16} />
              {t.label}
              {locked && <AppIcon name="sparkles" size={13} />}
            </button>
          );
        })}
      </nav>

      {unlocked === null ? (
        <div style={{ color: 'var(--text-muted)', padding: 40, textAlign: 'center' }}>
          Loading your plan…
        </div>
      ) : !isUnlocked(tab) ? (
        <LockedFeature title={meta.label} description={meta.lockedCopy} />
      ) : (
        <>
          {tab === 'COUPONS' && <CouponsTab isAdmin={isAdmin} />}
          {tab === 'CAMPAIGNS' && (
            <CampaignsTab
              key={campaignSegmentId ?? 'none'}
              isAdmin={isAdmin}
              hasSegments={isUnlocked('SEGMENTS')}
              initialSegmentId={campaignSegmentId}
              onOpenChannels={() => setTab('CHANNELS')}
            />
          )}
          {tab === 'CHANNELS' && <ChannelsTab isAdmin={isAdmin} />}
          {tab === 'LOYALTY' && <LoyaltyTab isAdmin={isAdmin} />}
          {tab === 'REFERRALS' && <ReferralsTab isAdmin={isAdmin} />}
          {tab === 'GIFT_CARDS' && <GiftCardsTab isAdmin={isAdmin} />}
          {tab === 'SEGMENTS' && (
            <SegmentsTab isAdmin={isAdmin} onCampaignTo={openCampaignForSegment} />
          )}
          {tab === 'REVIEWS' && <ReviewsTab isAdmin={isAdmin} />}
        </>
      )}
    </div>
  );
};
