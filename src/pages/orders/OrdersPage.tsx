import React, { useState } from 'react';
import { useOrderStore } from '../../stores/orderStore';
import { useModalStore } from '../../stores/modalStore';
import { DataTable, Column } from '../../components/tables/DataTable';
import { StatusPill } from '../../components/feedback/StatusPill';
import { Order, OrderStatus } from '../../data/mockData';
import { TextInput } from '../../components/forms/TextInput';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon, IconName } from '../../components/icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';

type Tab = 'ACTIVE' | 'HISTORY';
type ActiveStatus = 'ALL' | 'CONFIRMING' | 'ACCEPTED' | 'PREPARING' | 'OUT_FOR_DELIVERY';
type HistoryStatus = 'ALL' | 'COMPLETE' | 'CANCELLED';
type DateRange = 'TODAY' | 'WEEK' | 'MONTH' | 'ALL';

const parseOrderDate = (dateString: string): Date => {
  try {
    const parsed = new Date(dateString);
    return isNaN(parsed.getTime()) ? new Date(0) : parsed;
  } catch {
    return new Date(0);
  }
};

const isWithinRange = (date: Date, range: DateRange): boolean => {
  if (range === 'ALL') return true;
  const now = new Date();
  const start = new Date(now);
  start.setHours(0, 0, 0, 0);
  if (range === 'TODAY') return date >= start;
  if (range === 'WEEK') {
    start.setDate(start.getDate() - start.getDay());
    return date >= start;
  }
  if (range === 'MONTH') {
    start.setDate(1);
    return date >= start;
  }
  return true;
};

export const OrdersPage: React.FC = () => {
  const { orders, updateStatus, updatePaymentStatus } = useOrderStore();
  const { openModal } = useModalStore();
  const money = useMoney();
  const [activeTab, setActiveTab] = useState<Tab>('ACTIVE');
  const [search, setSearch] = useState('');
  const [activeStatus, setActiveStatus] = useState<ActiveStatus>('ALL');
  const [historyStatus, setHistoryStatus] = useState<HistoryStatus>('ALL');
  const [dateRange, setDateRange] = useState<DateRange>('ALL');

  const statusFilter = activeTab === 'ACTIVE' ? activeStatus : historyStatus;
  const setStatusFilter = activeTab === 'ACTIVE' ? setActiveStatus : setHistoryStatus;

  const filtered = orders.filter(o => {
    const inActiveTab = o.status !== 'COMPLETE' && o.status !== 'CANCELLED';
    const inHistoryTab = o.status === 'COMPLETE' || o.status === 'CANCELLED';
    if (activeTab === 'ACTIVE' && !inActiveTab) return false;
    if (activeTab === 'HISTORY' && !inHistoryTab) return false;

    const matchSearch = o.id.toLowerCase().includes(search.toLowerCase()) ||
      o.customerName.toLowerCase().includes(search.toLowerCase());
    const matchStatus = statusFilter === 'ALL' || o.status === statusFilter;
    const matchDate = isWithinRange(parseOrderDate(o.date), dateRange);
    return matchSearch && matchStatus && matchDate;
  });

  const activeOrders = orders.filter(o => o.status !== 'COMPLETE' && o.status !== 'CANCELLED');
  const historyOrders = orders.filter(o => o.status === 'COMPLETE' || o.status === 'CANCELLED');

  const totalRevenue = filtered.reduce((s, o) => s + o.total, 0);
  const completedCount = orders.filter(o => o.status === 'COMPLETE').length;
  const cancelledCount = orders.filter(o => o.status === 'CANCELLED').length;
  const pendingCount = activeOrders.filter(o => o.status !== 'CANCELLED').length;

  const nextStatus = (order: Order): OrderStatus | null => {
    if (order.status === 'CANCELLED' || order.status === 'COMPLETE') return null;
    if (order.status === 'CONFIRMING') return 'ACCEPTED';
    if (order.status === 'ACCEPTED') return 'PREPARING';
    if (order.status === 'PREPARING') return order.fulfillment === 'DELIVERY' ? 'OUT_FOR_DELIVERY' : 'COMPLETE';
    if (order.status === 'OUT_FOR_DELIVERY') return 'COMPLETE';
    return null;
  };

  const columns: Column<Order>[] = [
    {
      key: 'id',
      header: 'Order ID',
      render: row => <strong style={{ color: 'var(--dorzak-primary)', fontFamily: 'monospace' }}>{row.id}</strong>
    },
    {
      key: 'customerName',
      header: 'Customer',
      render: row => (
        <div>
          <strong style={{ fontSize: '0.9rem' }}>{row.customerName}</strong>
          {row.fulfillment === 'DINE_IN' && row.tableNumber && <div style={{ fontSize: '0.75rem', color: 'var(--dorzak-primary)', fontWeight: 800 }}>Table {row.tableNumber}</div>}
          {row.customerPhone && <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{row.customerPhone}</div>}
        </div>
      )
    },
    { key: 'date', header: 'Date & Time' },
    {
      key: 'fulfillment',
      header: 'Fulfillment',
      render: row => (
        <span style={{ fontSize: '0.85rem', fontWeight: 600, textTransform: 'capitalize' }}>
          {(row.fulfillment ?? 'PICKUP').replace(/_/g, ' ').toLowerCase()}
        </span>
      )
    },
    {
      key: 'paymentMethod',
      header: 'Payment',
      render: row => {
        const icons: Record<string, IconName> = { CARD: 'card', CASH: 'cash', TRANSFER: 'transfer', WHATSAPP: 'whatsapp', FAWRAN: 'transfer' };
        return (
          <div style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '0.85rem', fontWeight: 600 }}>
            <AppIcon name={icons[row.paymentMethod]} size={15} /> {row.paymentMethod}
          </div>
        );
      }
    },
    {
      key: 'status',
      header: 'Status',
      render: row => (
        <div style={{ display: 'grid', gap: 4 }}>
          <StatusPill status={row.status} />
          <StatusPill status={row.paymentStatus} />
          {row.courierState && (
            <span style={{
              display: 'inline-block',
              padding: '2px 8px',
              borderRadius: '999px',
              fontSize: '0.72rem',
              fontWeight: 700,
              backgroundColor: 'var(--color-bg)',
              border: '1px solid var(--color-border)',
              color: 'var(--text-muted)',
              whiteSpace: 'nowrap',
            }}>
              🚚 {row.courierState}
            </span>
          )}
        </div>
      )
    },
    {
      key: 'total',
      header: 'Total',
      render: row => <strong style={{ fontSize: '1rem', color: 'var(--dorzak-success)' }}>{money(row.total)}</strong>
    },
    {
      key: 'actions',
      header: '',
      render: row => {
        const canAdvance = activeTab === 'ACTIVE' && row.status !== 'COMPLETE' && nextStatus(row);
        const canCancel = row.status !== 'CANCELLED' && row.status !== 'COMPLETE';
        const canRefund = row.status === 'CANCELLED' && (row.paymentStatus === 'PAID' || row.paymentStatus === 'PENDING_VERIFICATION');
        return (
        <div style={{ display: 'flex', gap: '6px' }}>
          {canCancel && (
            <AppButton variant="secondary" style={{ borderColor: 'var(--dorzak-danger, #d32f2f)', color: 'var(--dorzak-danger, #d32f2f)' }} onClick={async e => { e.stopPropagation(); await updateStatus(row, 'CANCELLED'); }}>
              Cancel
            </AppButton>
          )}
          {canRefund && (
            <AppButton variant="secondary" style={{ borderColor: 'var(--dorzak-danger, #d32f2f)', color: 'var(--dorzak-danger, #d32f2f)' }} onClick={async e => { e.stopPropagation(); await updatePaymentStatus(row, 'REFUNDED'); }}>
              Refund
            </AppButton>
          )}
          {activeTab === 'ACTIVE' && row.paymentMethod === 'FAWRAN' && row.paymentStatus === 'PENDING_VERIFICATION' && (
            <AppButton variant="secondary" onClick={async e => { e.stopPropagation(); await updatePaymentStatus(row, 'PAID'); }}>
              Verify Payment
            </AppButton>
          )}
          {row.hasPaymentProof && row.apiId && (
            <AppButton variant="secondary" onClick={e => {
              e.stopPropagation();
              openModal('PAYMENT_PROOF', { orderId: Number(row.apiId), orderLabel: row.id });
            }}>View Proof</AppButton>
          )}
          {canAdvance && (
            <AppButton variant="primary" onClick={async e => { e.stopPropagation(); await updateStatus(row, nextStatus(row)!); }}>
              {nextStatus(row)!.replace(/_/g, ' ')}
            </AppButton>
          )}
          <AppButton
            variant="secondary"
            style={{ padding: '4px 10px', fontSize: '0.8rem' }}
            onClick={e => { e.stopPropagation(); openModal('ORDER_DETAIL', { order: row }); }}
          >
            <AppIcon name="orders" size={13} /> Details
          </AppButton>
        </div>
        );
      }
    }
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      {/* Header */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '12px' }}>
        <div>
          <h2 style={{ margin: 0 }}>Orders</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Track active orders and browse fulfilled order history.</span>
        </div>
        <AppButton variant="secondary" onClick={() => openModal('RECEIPT')}>
          <AppIcon name="orders" size={15} /> Export CSV
        </AppButton>
      </div>

      {/* Tabs */}
      <div style={{ display: 'flex', gap: '8px', borderBottom: '1px solid var(--color-border)' }}>
        {(['ACTIVE', 'HISTORY'] as Tab[]).map(tab => (
          <button
            key={tab}
            type="button"
            onClick={() => setActiveTab(tab)}
            style={{
              padding: '10px 18px',
              border: 'none',
              background: 'none',
              borderBottom: `2px solid ${activeTab === tab ? 'var(--dorzak-primary)' : 'transparent'}`,
              color: activeTab === tab ? 'var(--dorzak-primary)' : 'var(--text-muted)',
              fontWeight: 700,
              fontSize: '0.9rem',
              cursor: 'pointer',
              marginBottom: '-1px'
            }}
          >
            {tab === 'ACTIVE' ? `Active (${activeOrders.length})` : `History (${historyOrders.length})`}
          </button>
        ))}
      </div>

      {/* Summary Pills */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '14px' }}>
        <div className="card" style={{ padding: '14px', display: 'flex', gap: '12px', alignItems: 'center' }}>
          <div style={{ width: '40px', height: '40px', borderRadius: '4px', backgroundColor: 'var(--dorzak-success-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--dorzak-success)' }}><AppIcon name="dollar" size={20} /></div>
          <div>
            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600 }}>{activeTab === 'ACTIVE' ? 'ACTIVE ORDER VALUE' : 'HISTORY REVENUE'}</div>
            <div style={{ fontSize: '1.2rem', fontWeight: 700, color: 'var(--dorzak-success)' }}>{money(totalRevenue)}</div>
          </div>
        </div>
        <div className="card" style={{ padding: '14px', display: 'flex', gap: '12px', alignItems: 'center' }}>
          <div style={{ width: '40px', height: '40px', borderRadius: '4px', backgroundColor: 'var(--dorzak-success-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--dorzak-success)' }}><AppIcon name="checkCircle" size={20} /></div>
          <div>
            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600 }}>COMPLETED</div>
            <div style={{ fontSize: '1.2rem', fontWeight: 700, color: 'var(--dorzak-primary)' }}>{completedCount}</div>
          </div>
        </div>
        <div className="card" style={{ padding: '14px', display: 'flex', gap: '12px', alignItems: 'center' }}>
          <div style={{ width: '40px', height: '40px', borderRadius: '4px', backgroundColor: activeTab === 'ACTIVE' ? 'var(--color-warning-soft)' : 'var(--dorzak-danger-light, #fdecec)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: activeTab === 'ACTIVE' ? 'var(--color-warning)' : 'var(--dorzak-danger, #d32f2f)' }}><AppIcon name={activeTab === 'ACTIVE' ? 'alert' : 'close'} size={20} /></div>
          <div>
            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 600 }}>{activeTab === 'ACTIVE' ? 'PENDING' : 'CANCELLED'}</div>
            <div style={{ fontSize: '1.2rem', fontWeight: 700, color: activeTab === 'ACTIVE' ? 'var(--dorzak-warning)' : 'var(--dorzak-danger, #d32f2f)' }}>{activeTab === 'ACTIVE' ? pendingCount : cancelledCount}</div>
          </div>
        </div>
      </div>

      {/* Filters Row */}
      <div style={{ display: 'flex', gap: '12px', alignItems: 'center', flexWrap: 'wrap' }}>
        <div style={{ flex: 1, minWidth: '200px' }}>
          <TextInput
            placeholder="Search by Order ID or Customer name..."
            icon="search"
            value={search}
            onChange={e => setSearch(e.target.value)}
          />
        </div>
        <div style={{ display: 'flex', gap: '6px', flexWrap: 'wrap' }}>
          {(activeTab === 'ACTIVE'
            ? ['ALL', 'CONFIRMING', 'ACCEPTED', 'PREPARING', 'OUT_FOR_DELIVERY']
            : ['ALL', 'COMPLETE', 'CANCELLED']
          ).map(s => (
            <button key={s} type="button" onClick={() => setStatusFilter(s as any)}
              style={{
                padding: '7px 14px',
                borderRadius: '999px',
                border: '1px solid var(--color-border)',
                backgroundColor: statusFilter === s ? 'var(--dorzak-primary)' : 'var(--color-surface)',
                color: statusFilter === s ? '#fff' : 'var(--text-main)',
                fontSize: '0.82rem', fontWeight: 600, cursor: 'pointer'
              }}>
              {s === 'ALL' ? 'All' : s.replace(/_/g, ' ').toLowerCase()}
            </button>
          ))}
        </div>
        <div style={{ display: 'flex', gap: '6px' }}>
          {(['TODAY', 'WEEK', 'MONTH', 'ALL'] as DateRange[]).map(r => (
            <button key={r} type="button" onClick={() => setDateRange(r)}
              style={{
                padding: '7px 12px',
                borderRadius: '6px',
                border: '1px solid var(--color-border)',
                backgroundColor: dateRange === r ? 'var(--color-bg)' : 'var(--color-surface)',
                color: 'var(--text-main)',
                fontSize: '0.8rem', fontWeight: 600, cursor: 'pointer'
              }}>
              {r === 'ALL' ? 'All time' : r.toLowerCase()}
            </button>
          ))}
        </div>
      </div>

      <DataTable
        columns={columns}
        data={filtered}
        keyExtractor={o => o.id}
        onRowClick={o => openModal('ORDER_DETAIL', { order: o })}
      />
    </div>
  );
};
