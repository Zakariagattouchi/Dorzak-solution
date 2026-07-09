import React, { useMemo } from 'react';
import { BaseModal } from './BaseModal';
import { AppButton } from '../buttons/AppButton';
import { useModalStore } from '../../stores/modalStore';
import { useOrderStore } from '../../stores/orderStore';
import { useSettingsStore } from '../../stores/settingsStore';
import { Order, OrderItem, OrderStatus } from '../../data/mockData';
import { StatusPill } from '../feedback/StatusPill';
import { AppIcon, IconName } from '../icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';
import { orderApi } from '../../api/endpoints';

const statusFlow: OrderStatus[] = ['CONFIRMING', 'ACCEPTED', 'PREPARING', 'OUT_FOR_DELIVERY', 'COMPLETE'];

export const OrderDetailModal: React.FC = () => {
  const { activeModal, payload, openModal, closeModal } = useModalStore();
  const { updateStatus, updatePaymentStatus, orders } = useOrderStore();
  const { accountInfo } = useSettingsStore();
  const money = useMoney();

  const order = useMemo(() => {
    if (!payload?.order) return null;
    return orders.find((o) => o.id === payload.order.id) || payload.order;
  }, [payload?.order, orders]);

  const nextStatus = useMemo((): OrderStatus | null => {
    if (!order) return null;
    if (order.status === 'CANCELLED' || order.status === 'COMPLETE') return null;
    if (order.status === 'CONFIRMING') return 'ACCEPTED';
    if (order.status === 'ACCEPTED') return 'PREPARING';
    if (order.status === 'PREPARING') return order.fulfillment === 'DELIVERY' ? 'OUT_FOR_DELIVERY' : 'COMPLETE';
    if (order.status === 'OUT_FOR_DELIVERY') return 'COMPLETE';
    return null;
  }, [order]);

  if (!order) return null;

  const paymentIcon: Record<string, IconName> = { CARD: 'card', CASH: 'cash', TRANSFER: 'transfer', WHATSAPP: 'whatsapp', FAWRAN: 'transfer' };
  const isHistory = order.status === 'COMPLETE' || order.status === 'CANCELLED';

  const handleNextStatus = async () => {
    if (!nextStatus) return;
    await updateStatus(order, nextStatus);
  };

  const handleVerifyPayment = async () => {
    await updatePaymentStatus(order, 'PAID');
  };

  const handleViewProof = async () => {
    if (!order.apiId) return;
    const blob = await orderApi.paymentProof(Number(order.apiId));
    window.open(URL.createObjectURL(blob), '_blank', 'noopener,noreferrer');
  };

  const handlePrintReceipt = () => {
    openModal('RECEIPT', { order });
  };

  return (
    <BaseModal
      isOpen={activeModal === 'ORDER_DETAIL'}
      onClose={closeModal}
      title={`Order ${order.id}`}
      footer={
        <>
          {order.status !== 'CANCELLED' && order.status !== 'COMPLETE' && (
            <AppButton variant="secondary" style={{ borderColor: 'var(--dorzak-danger, #d32f2f)', color: 'var(--dorzak-danger, #d32f2f)' }} onClick={async () => { await updateStatus(order, 'CANCELLED'); closeModal(); }}>Cancel</AppButton>
          )}
          {order.status === 'CANCELLED' && (order.paymentStatus === 'PAID' || order.paymentStatus === 'PENDING_VERIFICATION') && (
            <AppButton variant="secondary" style={{ borderColor: 'var(--dorzak-danger, #d32f2f)', color: 'var(--dorzak-danger, #d32f2f)' }} onClick={async () => { await updatePaymentStatus(order, 'REFUNDED'); }}>Refund</AppButton>
          )}
          {!isHistory && order.paymentMethod === 'FAWRAN' && order.paymentStatus === 'PENDING_VERIFICATION' && (
            <AppButton variant="primary" onClick={handleVerifyPayment}>Verify Payment</AppButton>
          )}
          {!isHistory && nextStatus && (
            <AppButton variant="primary" onClick={handleNextStatus}>
              {nextStatus.replace(/_/g, ' ')}
            </AppButton>
          )}
          {order.hasPaymentProof && order.apiId && (
            <AppButton variant="secondary" onClick={handleViewProof}>View Proof</AppButton>
          )}
          <AppButton variant="secondary" onClick={handlePrintReceipt} icon="orders">Receipt</AppButton>
          <AppButton variant="secondary" onClick={closeModal}>Close</AppButton>
        </>
      }
    >
      <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
        {/* Header */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '12px' }}>
          <div>
            <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{order.date}</div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginTop: '6px' }}>
              <StatusPill status={order.status} />
              <StatusPill status={order.paymentStatus} />
            </div>
          </div>
          <div style={{ textAlign: 'right' }}>
            <div style={{ fontSize: '1.3rem', fontWeight: 700, color: 'var(--dorzak-success)' }}>{money(order.total, 2, order.currencyCode)}</div>
            <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: '6px' }}>
              <AppIcon name={paymentIcon[order.paymentMethod]} size={14} /> {order.paymentMethod}
            </div>
          </div>
        </div>

        {/* Progress */}
        {!isHistory && (
          <div className="card" style={{ padding: '14px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', position: 'relative' }}>
              {statusFlow.map((step, idx) => {
                const currentIdx = statusFlow.indexOf(order.status);
                const active = idx <= currentIdx;
                return (
                  <div key={step} style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '6px', flex: 1 }}>
                    <div style={{
                      width: '28px', height: '28px', borderRadius: '50%',
                      backgroundColor: active ? 'var(--dorzak-primary)' : 'var(--color-surface)',
                      border: `2px solid ${active ? 'var(--dorzak-primary)' : 'var(--color-border)'}`,
                      color: active ? '#fff' : 'var(--text-muted)',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      fontSize: '0.75rem', fontWeight: 700, zIndex: 1
                    }}>
                      {idx + 1}
                    </div>
                    <span style={{ fontSize: '0.7rem', fontWeight: 600, color: active ? 'var(--text-main)' : 'var(--text-muted)', textAlign: 'center' }}>
                      {step.replace(/_/g, ' ')}
                    </span>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* Customer & Fulfillment */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '14px' }}>
          <div className="card" style={{ padding: '14px' }}>
            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 700, marginBottom: '8px' }}>CUSTOMER</div>
            <div style={{ fontWeight: 700, fontSize: '1rem' }}>{order.customerName}</div>
            {order.customerPhone && <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginTop: '4px' }}>{order.customerPhone}</div>}
          </div>

          <div className="card" style={{ padding: '14px' }}>
            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 700, marginBottom: '8px' }}>FULFILLMENT</div>
            <div style={{ fontWeight: 700, fontSize: '1rem', textTransform: 'capitalize' }}>{(order.fulfillment ?? 'PICKUP').replace(/_/g, ' ').toLowerCase()}</div>
            {order.fulfillment === 'DINE_IN' && order.tableNumber && (
              <div style={{ fontSize: '0.85rem', color: 'var(--dorzak-primary)', fontWeight: 700, marginTop: '4px' }}>Table {order.tableNumber}</div>
            )}
            {order.fulfillment === 'DELIVERY' && (
              <div style={{ fontSize: '0.85rem', color: 'var(--text-muted)', marginTop: '4px' }}>
                {order.deliveryAddress}{order.deliveryCity ? `, ${order.deliveryCity}` : ''}
              </div>
            )}
          </div>
        </div>

        {/* Items */}
        <div className="card" style={{ padding: '0', overflow: 'hidden' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.9rem' }}>
            <thead>
              <tr style={{ backgroundColor: 'var(--color-bg)', textAlign: 'left' }}>
                <th style={{ padding: '10px 14px' }}>Item</th>
                <th style={{ padding: '10px 14px', textAlign: 'center' }}>Qty</th>
                <th style={{ padding: '10px 14px', textAlign: 'right' }}>Unit</th>
                <th style={{ padding: '10px 14px', textAlign: 'right' }}>Total</th>
              </tr>
            </thead>
            <tbody>
              {order.items.map((item: OrderItem, idx: number) => (
                <tr key={idx} style={{ borderBottom: '1px solid var(--color-border)' }}>
                  <td style={{ padding: '10px 14px' }}>
                    <div style={{ fontWeight: 600 }}>{item.productName}</div>
                    {item.variantName && <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{item.variantName}</div>}
                  </td>
                  <td style={{ padding: '10px 14px', textAlign: 'center' }}>{item.quantity}</td>
                  <td style={{ padding: '10px 14px', textAlign: 'right' }}>{money(item.unitPrice, 2, order.currencyCode)}</td>
                  <td style={{ padding: '10px 14px', textAlign: 'right', fontWeight: 600 }}>{money(item.unitPrice * item.quantity, 2, order.currencyCode)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Totals */}
        <div className="card" style={{ padding: '14px', display: 'flex', flexDirection: 'column', gap: '6px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <span style={{ color: 'var(--text-muted)' }}>Subtotal</span>
            <span>{money(order.subtotal, 2, order.currencyCode)}</span>
          </div>
          {order.taxAmount > 0 && (
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span style={{ color: 'var(--text-muted)' }}>Tax</span>
              <span>{money(order.taxAmount, 2, order.currencyCode)}</span>
            </div>
          )}
          {order.discount > 0 && (
            <div style={{ display: 'flex', justifyContent: 'space-between', color: 'var(--dorzak-success)' }}>
              <span>Discount</span>
              <span>-{money(order.discount, 2, order.currencyCode)}</span>
            </div>
          )}
          <div style={{ display: 'flex', justifyContent: 'space-between', fontWeight: 700, fontSize: '1.1rem', marginTop: '6px', paddingTop: '8px', borderTop: '1px dashed var(--color-border)' }}>
            <span>Total</span>
            <span style={{ color: 'var(--dorzak-primary)' }}>{money(order.total, 2, order.currencyCode)}</span>
          </div>
        </div>

        {/* Notes */}
        {order.notes && (
          <div className="card" style={{ padding: '14px', backgroundColor: 'var(--color-bg)' }}>
            <div style={{ fontSize: '0.75rem', color: 'var(--text-muted)', fontWeight: 700, marginBottom: '6px' }}>NOTES</div>
            <div style={{ fontSize: '0.9rem' }}>{order.notes}</div>
          </div>
        )}
      </div>
    </BaseModal>
  );
};
