import React, { useState } from 'react';
import { useOrderStore } from '../../stores/orderStore';
import { DataTable, Column } from '../../components/tables/DataTable';
import { StatusPill } from '../../components/feedback/StatusPill';
import { Order } from '../../data/mockData';
import { TextInput } from '../../components/forms/TextInput';
import { AppIcon, IconName } from '../../components/icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';

type PayFilter = 'ALL' | 'CARD' | 'CASH' | 'TRANSFER' | 'WHATSAPP';

export const SalesPage: React.FC = () => {
  const { orders } = useOrderStore();
  const money = useMoney();
  const [search, setSearch] = useState('');
  const [payFilter, setPayFilter] = useState<PayFilter>('ALL');

  const filtered = orders.filter(o => {
    if (o.status !== 'COMPLETE') return false;
    const matchSearch = o.id.toLowerCase().includes(search.toLowerCase()) ||
      o.customerName.toLowerCase().includes(search.toLowerCase());
    const matchPay = payFilter === 'ALL' || o.paymentMethod === payFilter;
    return matchSearch && matchPay;
  });

  const totalRevenue = filtered.reduce((s, o) => s + o.total, 0);
  const totalTax = filtered.reduce((s, o) => s + o.taxAmount, 0);
  const totalDiscount = filtered.reduce((s, o) => s + o.discount, 0);

  const columns: Column<Order>[] = [
    {
      key: 'id',
      header: 'Transaction ID',
      render: row => <strong style={{ color: 'var(--dorzak-primary)', fontFamily: 'monospace' }}>{row.id}</strong>
    },
    {
      key: 'customerName',
      header: 'Customer',
      render: row => (
        <div>
          <strong style={{ fontSize: '0.88rem' }}>{row.customerName}</strong>
          {row.customerPhone && <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{row.customerPhone}</div>}
        </div>
      )
    },
    { key: 'date', header: 'Date & Time' },
    {
      key: 'paymentMethod',
      header: 'Payment Method',
      render: row => {
        const icons: Record<string, IconName> = { CARD: 'card', CASH: 'cash', TRANSFER: 'transfer', WHATSAPP: 'whatsapp' };
        const colors: Record<string, string> = { CARD: 'var(--dorzak-primary)', CASH: 'var(--dorzak-success)', TRANSFER: 'var(--color-info)', WHATSAPP: 'var(--dorzak-success)' };
        return (
          <span style={{
            display: 'inline-flex', gap: '5px', alignItems: 'center',
            padding: '3px 10px', borderRadius: '999px', fontSize: '0.8rem', fontWeight: 700,
            backgroundColor: `${colors[row.paymentMethod]}1a`, color: colors[row.paymentMethod]
          }}>
            <AppIcon name={icons[row.paymentMethod]} size={15} /> {row.paymentMethod}
          </span>
        );
      }
    },
    {
      key: 'status',
      header: 'Status',
      render: row => <StatusPill status={row.status} />
    },
    {
      key: 'discount',
      header: 'Discount',
      render: row => row.discount > 0
        ? <span style={{ color: 'var(--dorzak-danger)', fontWeight: 600 }}>-{money(row.discount)}</span>
        : <span style={{ color: 'var(--text-muted)' }}>—</span>
    },
    {
      key: 'taxAmount',
      header: 'Tax',
      render: row => <span style={{ color: 'var(--text-muted)' }}>{money(row.taxAmount)}</span>
    },
    {
      key: 'total',
      header: 'Net Total',
      render: row => <strong style={{ fontSize: '1rem', color: 'var(--dorzak-success)' }}>{money(row.total)}</strong>
    }
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      <div>
        <h2 style={{ margin: 0 }}>Sales Transactions Log</h2>
        <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Complete audit log of all sales with payment breakdowns, discounts, and tax details</span>
      </div>

      {/* Summary Row */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '14px' }}>
        <div className="card" style={{ padding: '14px' }}>
          <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600 }}>GROSS REVENUE</div>
          <div style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--dorzak-success)' }}>{money(totalRevenue)}</div>
        </div>
        <div className="card" style={{ padding: '14px' }}>
          <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600 }}>TOTAL TAX COLLECTED</div>
          <div style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--dorzak-primary)' }}>{money(totalTax)}</div>
        </div>
        <div className="card" style={{ padding: '14px' }}>
          <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600 }}>DISCOUNTS GIVEN</div>
          <div style={{ fontSize: '1.5rem', fontWeight: 800, color: 'var(--dorzak-danger)' }}>-{money(totalDiscount)}</div>
        </div>
      </div>

      {/* Filter row */}
      <div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
        <div style={{ flex: 1, minWidth: '200px' }}>
          <TextInput
            placeholder="Search by transaction ID or customer..."
            icon="search"
            value={search}
            onChange={e => setSearch(e.target.value)}
          />
        </div>
        <div style={{ display: 'flex', gap: '6px' }}>
          {(['ALL', 'CARD', 'CASH', 'TRANSFER', 'WHATSAPP'] as PayFilter[]).map(pf => (
            <button key={pf} type="button" onClick={() => setPayFilter(pf)} style={{
              padding: '7px 14px', borderRadius: '999px',
              border: '1px solid var(--color-border)',
              backgroundColor: payFilter === pf ? 'var(--dorzak-primary)' : 'var(--color-surface)',
              color: payFilter === pf ? '#fff' : 'var(--text-main)',
              fontSize: '0.82rem', fontWeight: 600, cursor: 'pointer'
            }}>
              {pf === 'ALL' ? 'All Methods' : pf}
            </button>
          ))}
        </div>
      </div>

      <DataTable
        columns={columns}
        data={filtered}
        keyExtractor={o => o.id}
      />
    </div>
  );
};
