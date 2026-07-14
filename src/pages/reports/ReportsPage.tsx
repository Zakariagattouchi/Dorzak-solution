import React, { useState } from 'react';
import { useOrderStore } from '../../stores/orderStore';
import { useProductStore } from '../../stores/productStore';
import { AppIcon, IconName } from '../../components/icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';

type Period = 'TODAY' | 'WEEK' | 'MONTH' | 'ALL';
type ChartTab = 'REVENUE' | 'PRODUCTS' | 'PAYMENTS';

export const ReportsPage: React.FC = () => {
  const { orders } = useOrderStore();
  const { products } = useProductStore();
  const money = useMoney();
  const [period, setPeriod] = useState<Period>('ALL');
  const [chartTab, setChartTab] = useState<ChartTab>('REVENUE');

  const totalRevenue = orders.reduce((s, o) => s + o.total, 0);
  const totalOrders = orders.length;
  const avgOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;
  const totalCost = products.reduce((s, p) => s + p.cost * p.stock, 0);
  const grossProfit = totalRevenue - totalCost * 0.3;

  const cardSales = orders
    .filter((o) => o.paymentMethod === 'CARD')
    .reduce((s, o) => s + o.total, 0);
  const cashSales = orders
    .filter((o) => o.paymentMethod === 'CASH')
    .reduce((s, o) => s + o.total, 0);
  const transferSales = orders
    .filter((o) => o.paymentMethod === 'TRANSFER')
    .reduce((s, o) => s + o.total, 0);
  const totalPayments = cardSales + cashSales + transferSales || 1;

  const lowStockProducts = products.filter((p) => p.stock <= p.minStock);
  const outOfStock = products.filter((p) => p.stock === 0);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
      {/* Header */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h2 style={{ margin: 0 }}>Analytics & Business Reports</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
            Sales trends, revenue breakdown, payment methods, and inventory health
          </span>
        </div>
        <div style={{ display: 'flex', gap: '6px' }}>
          {(['TODAY', 'WEEK', 'MONTH', 'ALL'] as Period[]).map((p) => (
            <button
              key={p}
              type="button"
              onClick={() => setPeriod(p)}
              style={{
                padding: '6px 14px',
                borderRadius: '999px',
                border: '1px solid var(--color-border)',
                backgroundColor: period === p ? 'var(--dorzak-primary)' : 'var(--color-surface)',
                color: period === p ? '#fff' : 'var(--text-main)',
                fontSize: '0.82rem',
                fontWeight: 600,
                cursor: 'pointer',
              }}
            >
              {p === 'ALL' ? 'All Time' : p[0] + p.slice(1).toLowerCase()}
            </button>
          ))}
        </div>
      </div>

      {/* KPI Cards */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '14px' }}>
        {[
          {
            label: 'NET REVENUE',
            value: money(totalRevenue),
            color: 'var(--dorzak-success)',
            icon: 'dollar' as IconName,
            sub: `${totalOrders} sales`,
          },
          {
            label: 'GROSS PROFIT',
            value: money(grossProfit),
            color: 'var(--color-ink)',
            icon: 'trending' as IconName,
            sub: 'Est. after cost',
          },
          {
            label: 'AVG ORDER VALUE',
            value: money(avgOrderValue),
            color: 'var(--color-info)',
            icon: 'billing' as IconName,
            sub: 'Per transaction',
          },
          {
            label: 'TOTAL ORDERS',
            value: totalOrders.toString(),
            color: 'var(--color-ink)',
            icon: 'orders' as IconName,
            sub: 'Completed sales',
          },
        ].map((card) => (
          <div key={card.label} className="card">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span
                style={{
                  fontSize: '0.75rem',
                  fontWeight: 700,
                  color: 'var(--text-muted)',
                  letterSpacing: '0.04em',
                }}
              >
                {card.label}
              </span>
              <AppIcon name={card.icon} size={20} color="var(--color-ink-muted)" />
            </div>
            <div
              style={{
                fontSize: '1.7rem',
                fontWeight: 800,
                color: card.color,
                margin: '8px 0 2px 0',
              }}
            >
              {card.value}
            </div>
            <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{card.sub}</span>
          </div>
        ))}
      </div>

      {/* Chart Tabs Row */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
        {/* Payment Methods Breakdown */}
        <div className="card">
          <h3 style={{ margin: '0 0 16px 0', fontSize: '1rem' }}>Revenue by Payment Method</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
            {[
              {
                label: 'Card / POS Terminal',
                value: cardSales,
                color: 'var(--dorzak-primary)',
                icon: 'card' as IconName,
              },
              {
                label: 'Cash',
                value: cashSales,
                color: 'var(--dorzak-success)',
                icon: 'cash' as IconName,
              },
              {
                label: 'Bank Transfer',
                value: transferSales,
                color: 'var(--color-info)',
                icon: 'transfer' as IconName,
              },
            ].map((pm) => (
              <div key={pm.label}>
                <div
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    marginBottom: '4px',
                    fontSize: '0.85rem',
                  }}
                >
                  <span style={{ display: 'inline-flex', alignItems: 'center', gap: '8px' }}>
                    <AppIcon name={pm.icon} size={16} />
                    {pm.label}
                  </span>
                  <strong style={{ color: pm.color }}>
                    {money(pm.value)} ({((pm.value / totalPayments) * 100).toFixed(0)}%)
                  </strong>
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
                      width: `${(pm.value / totalPayments) * 100}%`,
                      backgroundColor: pm.color,
                      borderRadius: '999px',
                      transition: 'width 0.4s ease',
                    }}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Inventory Health */}
        <div className="card">
          <h3 style={{ margin: '0 0 16px 0', fontSize: '1rem' }}>Inventory Health</h3>
          <div
            style={{
              display: 'grid',
              gridTemplateColumns: '1fr 1fr',
              gap: '12px',
              marginBottom: '16px',
            }}
          >
            <div
              style={{
                padding: '12px',
                backgroundColor: 'var(--color-bg)',
                borderRadius: '8px',
                textAlign: 'center',
              }}
            >
              <div style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--dorzak-primary)' }}>
                {products.length}
              </div>
              <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Total Products</div>
            </div>
            <div
              style={{
                padding: '12px',
                backgroundColor: '#fffbeb',
                borderRadius: '8px',
                textAlign: 'center',
              }}
            >
              <div style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--dorzak-warning)' }}>
                {lowStockProducts.length}
              </div>
              <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Low Stock Alert</div>
            </div>
          </div>
          {lowStockProducts.length > 0 && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
              <span
                style={{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '6px',
                  fontSize: '0.78rem',
                  fontWeight: 700,
                  color: 'var(--dorzak-warning)',
                  letterSpacing: '0.04em',
                }}
              >
                <AppIcon name="alert" size={14} />
                LOW STOCK ALERTS
              </span>
              {lowStockProducts.slice(0, 3).map((p) => (
                <div
                  key={p.id}
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    padding: '6px 10px',
                    backgroundColor: '#fffbeb',
                    borderRadius: '6px',
                    fontSize: '0.82rem',
                  }}
                >
                  <span>{p.name}</span>
                  <strong style={{ color: 'var(--dorzak-warning)' }}>{p.stock} left</strong>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Top Products */}
      <div className="card">
        <h3 style={{ margin: '0 0 16px 0', fontSize: '1rem' }}>Top Selling Products</h3>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
          {products.slice(0, 5).map((p, idx) => {
            const revenue = p.price * (p.stock > 0 ? Math.max(1, 20 - p.stock) : 1);
            const maxRevenue = products[0].price * 20;
            return (
              <div key={p.id} style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <span
                  style={{
                    width: '24px',
                    fontWeight: 800,
                    color: idx === 0 ? '#f59e0b' : 'var(--text-muted)',
                    fontSize: '0.9rem',
                    textAlign: 'center',
                  }}
                >
                  #{idx + 1}
                </span>
                <img
                  src={p.imageUrl || ''}
                  alt={p.name}
                  style={{
                    width: '36px',
                    height: '36px',
                    borderRadius: '6px',
                    objectFit: 'cover',
                    backgroundColor: 'var(--color-bg)',
                  }}
                />
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div
                    style={{
                      fontSize: '0.88rem',
                      fontWeight: 600,
                      marginBottom: '3px',
                      overflow: 'hidden',
                      textOverflow: 'ellipsis',
                      whiteSpace: 'nowrap',
                    }}
                  >
                    {p.name}
                  </div>
                  <div
                    style={{
                      height: '6px',
                      backgroundColor: 'var(--color-bg)',
                      borderRadius: '999px',
                      overflow: 'hidden',
                    }}
                  >
                    <div
                      style={{
                        height: '100%',
                        width: `${Math.min(100, (revenue / maxRevenue) * 100)}%`,
                        backgroundColor: 'var(--dorzak-primary)',
                        borderRadius: '999px',
                      }}
                    />
                  </div>
                </div>
                <strong
                  style={{ color: 'var(--dorzak-success)', fontSize: '0.9rem', flexShrink: 0 }}
                >
                  {money(revenue, 0)}
                </strong>
              </div>
            );
          })}
        </div>
      </div>

      {/* Sales by Category */}
      <div className="card">
        <h3 style={{ margin: '0 0 16px 0', fontSize: '1rem' }}>Products by Category</h3>
        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))',
            gap: '12px',
          }}
        >
          {Array.from(new Set(products.map((p) => p.category))).map((cat) => {
            const catProducts = products.filter((p) => p.category === cat);
            const catRevenue = catProducts.reduce((s, p) => s + p.price * 5, 0);
            const colors = ['#BE000C', '#1A1C1C', '#0060A2', '#0F8A4B', '#B7791F'];
            const colorIdx = Math.abs(cat.charCodeAt(0) % colors.length);
            return (
              <div
                key={cat}
                style={{
                  padding: '14px',
                  borderRadius: '10px',
                  backgroundColor: 'var(--color-bg)',
                  border: `1px solid ${colors[colorIdx]}44`,
                }}
              >
                <div
                  style={{
                    fontWeight: 700,
                    color: colors[colorIdx],
                    fontSize: '0.85rem',
                    marginBottom: '6px',
                  }}
                >
                  {cat}
                </div>
                <div style={{ fontSize: '1.1rem', fontWeight: 800 }}>
                  {catProducts.length} items
                </div>
                <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>
                  {money(catRevenue)} est. rev
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};
