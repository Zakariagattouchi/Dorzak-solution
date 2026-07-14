import React, { useState } from 'react';
import { useOrderStore } from '../../stores/orderStore';
import { useSettingsStore } from '../../stores/settingsStore';
import { useToastStore } from '../../stores/toastStore';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon, IconName } from '../../components/icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';

type PeriodView = 'DAILY' | 'WEEKLY' | 'MONTHLY';

export const FinancesPage: React.FC = () => {
  const { orders } = useOrderStore();
  const { accountInfo } = useSettingsStore();
  const { addToast } = useToastStore();
  const money = useMoney();
  const [periodView, setPeriodView] = useState<PeriodView>('MONTHLY');

  const totalRevenue = orders.reduce((s, o) => s + o.total, 0);
  const cardSales = orders
    .filter((o) => o.paymentMethod === 'CARD')
    .reduce((s, o) => s + o.total, 0);
  const cashSales = orders
    .filter((o) => o.paymentMethod === 'CASH')
    .reduce((s, o) => s + o.total, 0);
  const transferSales = orders
    .filter((o) => o.paymentMethod === 'TRANSFER')
    .reduce((s, o) => s + o.total, 0);
  const totalTax = orders.reduce((s, o) => s + o.taxAmount, 0);
  const totalDiscount = orders.reduce((s, o) => s + o.discount, 0);
  const netRevenue = totalRevenue - totalTax;

  const completedOrders = orders.filter((o) => o.status === 'COMPLETE');
  const pendingRevenue = orders
    .filter((o) => !['COMPLETE', 'CANCELLED'].includes(o.status))
    .reduce((s, o) => s + o.total, 0);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h2 style={{ margin: 0 }}>Finances & Cash Flow</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
            Revenue breakdown, cash vs card flows, tax collected, and profit summary
          </span>
        </div>
        <div style={{ display: 'flex', gap: '8px' }}>
          {(['DAILY', 'WEEKLY', 'MONTHLY'] as PeriodView[]).map((p) => (
            <button
              key={p}
              type="button"
              onClick={() => setPeriodView(p)}
              style={{
                padding: '6px 14px',
                borderRadius: '999px',
                border: '1px solid var(--color-border)',
                backgroundColor:
                  periodView === p ? 'var(--dorzak-primary)' : 'var(--color-surface)',
                color: periodView === p ? '#fff' : 'var(--text-main)',
                fontSize: '0.82rem',
                fontWeight: 600,
                cursor: 'pointer',
              }}
            >
              {p[0] + p.slice(1).toLowerCase()}
            </button>
          ))}
        </div>
      </div>

      {/* Key Financial Metrics */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '14px' }}>
        {[
          {
            label: 'GROSS REVENUE',
            value: money(totalRevenue),
            color: 'var(--dorzak-success)',
            sub: `${completedOrders.length} completed sales`,
          },
          {
            label: 'NET REVENUE',
            value: money(netRevenue),
            color: 'var(--dorzak-primary)',
            sub: 'After tax deduction',
          },
          {
            label: 'TAX COLLECTED',
            value: money(totalTax),
            color: 'var(--color-info)',
            sub: `${accountInfo.taxRate}% rate applied`,
          },
          {
            label: 'PENDING REVENUE',
            value: money(pendingRevenue),
            color: 'var(--dorzak-warning)',
            sub: 'Awaiting payment',
          },
        ].map((card) => (
          <div key={card.label} className="card">
            <span
              style={{
                fontSize: '0.72rem',
                fontWeight: 700,
                color: 'var(--text-muted)',
                letterSpacing: '0.05em',
              }}
            >
              {card.label}
            </span>
            <div
              style={{
                fontSize: '1.6rem',
                fontWeight: 800,
                color: card.color,
                margin: '8px 0 4px 0',
              }}
            >
              {card.value}
            </div>
            <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{card.sub}</span>
          </div>
        ))}
      </div>

      {/* Cash Flow Breakdown */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
        {/* Payment Method Inflows */}
        <div className="card">
          <h3 style={{ margin: '0 0 16px 0', fontSize: '1rem' }}>Cash Flow by Method</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
            {[
              {
                label: 'Card / POS Terminal',
                amount: cardSales,
                icon: 'card' as IconName,
                color: 'var(--dorzak-primary)',
                pct: cardSales / (totalRevenue || 1),
              },
              {
                label: 'Cash in Register',
                amount: cashSales,
                icon: 'cash' as IconName,
                color: 'var(--dorzak-success)',
                pct: cashSales / (totalRevenue || 1),
              },
              {
                label: 'Bank Transfer / Pix',
                amount: transferSales,
                icon: 'transfer' as IconName,
                color: 'var(--color-info)',
                pct: transferSales / (totalRevenue || 1),
              },
            ].map((item) => (
              <div key={item.label}>
                <div
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginBottom: '6px',
                    fontSize: '0.85rem',
                  }}
                >
                  <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                    <AppIcon name={item.icon} size={16} />
                    <span>{item.label}</span>
                  </div>
                  <strong style={{ color: item.color }}>{money(item.amount)}</strong>
                </div>
                <div
                  style={{
                    height: '8px',
                    backgroundColor: 'var(--color-bg)',
                    borderRadius: '999px',
                    overflow: 'hidden',
                  }}
                >
                  <div
                    style={{
                      height: '100%',
                      width: `${item.pct * 100}%`,
                      backgroundColor: item.color,
                      borderRadius: '999px',
                      transition: 'width 0.4s ease',
                    }}
                  />
                </div>
                <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', marginTop: '3px' }}>
                  {(item.pct * 100).toFixed(1)}% of total
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Expense & Discount Summary */}
        <div className="card">
          <h3 style={{ margin: '0 0 16px 0', fontSize: '1rem' }}>Adjustments Summary</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                padding: '12px',
                backgroundColor: 'var(--color-bg)',
                borderRadius: '8px',
              }}
            >
              <div>
                <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontWeight: 600 }}>
                  DISCOUNTS GIVEN
                </div>
                <div style={{ fontSize: '1.2rem', fontWeight: 800, color: 'var(--dorzak-danger)' }}>
                  -{money(totalDiscount)}
                </div>
              </div>
              <AppIcon name="tag" size={22} color="var(--color-error)" />
            </div>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                padding: '12px',
                backgroundColor: 'var(--color-bg)',
                borderRadius: '8px',
              }}
            >
              <div>
                <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontWeight: 600 }}>
                  TAXES OWED
                </div>
                <div style={{ fontSize: '1.2rem', fontWeight: 800, color: 'var(--color-info)' }}>
                  {money(totalTax)}
                </div>
              </div>
              <AppIcon name="reports" size={22} color="var(--color-info)" />
            </div>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                padding: '12px',
                backgroundColor: '#f0fdf4',
                borderRadius: '8px',
                border: '1px solid #86efac',
              }}
            >
              <div>
                <div style={{ fontSize: '0.8rem', color: '#166534', fontWeight: 600 }}>
                  ESTIMATED NET
                </div>
                <div
                  style={{ fontSize: '1.2rem', fontWeight: 800, color: 'var(--dorzak-success)' }}
                >
                  {money(netRevenue)}
                </div>
              </div>
              <AppIcon name="checkCircle" size={22} color="var(--color-success)" />
            </div>
          </div>
        </div>
      </div>

      {/* Recent Transactions Table */}
      <div className="card">
        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            marginBottom: '16px',
          }}
        >
          <h3 style={{ margin: 0, fontSize: '1rem' }}>Recent Financial Entries</h3>
          <AppButton
            variant="secondary"
            type="button"
            onClick={() => addToast('Exporting financial report...', 'info')}
          >
            Export CSV
          </AppButton>
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '0' }}>
          <div
            style={{
              display: 'grid',
              gridTemplateColumns: '2fr 1fr 1fr 1fr 1fr',
              padding: '8px 12px',
              fontSize: '0.75rem',
              fontWeight: 700,
              color: 'var(--text-muted)',
              textTransform: 'uppercase',
              letterSpacing: '0.04em',
              borderBottom: '2px solid var(--color-border)',
            }}
          >
            <span>Description</span>
            <span>Date</span>
            <span>Method</span>
            <span>Tax</span>
            <span style={{ textAlign: 'right' }}>Amount</span>
          </div>
          {orders.map((o) => (
            <div
              key={o.id}
              style={{
                display: 'grid',
                gridTemplateColumns: '2fr 1fr 1fr 1fr 1fr',
                padding: '12px',
                fontSize: '0.85rem',
                borderBottom: '1px solid var(--color-border)',
                alignItems: 'center',
              }}
            >
              <div>
                <strong style={{ fontSize: '0.85rem' }}>Sale to {o.customerName}</strong>
                <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                  {o.id} • {o.items.length} item(s)
                </div>
              </div>
              <span style={{ color: 'var(--text-muted)' }}>{o.date}</span>
              <span style={{ fontSize: '0.8rem', fontWeight: 600 }}>{o.paymentMethod}</span>
              <span style={{ color: 'var(--color-info)', fontSize: '0.85rem' }}>
                +{money(o.taxAmount)}
              </span>
              <strong style={{ textAlign: 'end', color: 'var(--dorzak-success)' }}>
                {money(o.total)}
              </strong>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
