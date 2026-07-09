import React from 'react';
import { BaseModal } from './BaseModal';
import { AppButton } from '../buttons/AppButton';
import { useModalStore } from '../../stores/modalStore';
import { useSettingsStore } from '../../stores/settingsStore';
import { useToastStore } from '../../stores/toastStore';
import { Order } from '../../data/mockData';
import { StatusPill } from '../feedback/StatusPill';
import { useMoney } from '../../hooks/useMoney';

export const ReceiptModal: React.FC = () => {
  const { activeModal, payload, closeModal } = useModalStore();
  const { accountInfo } = useSettingsStore();
  const { addToast } = useToastStore();
  const money = useMoney();

  const order: Order | null = payload?.order || null;

  if (!order) return null;

  const handlePrint = () => {
    addToast(`Printing receipt for Order ${order.id}...`, 'info');
    window.print();
  };

  return (
    <BaseModal
      isOpen={activeModal === 'RECEIPT'}
      onClose={closeModal}
      title={`Sales Receipt — ${order.id}`}
      footer={
        <>
          <AppButton variant="secondary" onClick={closeModal}>Close</AppButton>
          <AppButton variant="primary" onClick={handlePrint} icon="orders">Print Receipt</AppButton>
        </>
      }
    >
      <div style={{ padding: '16px', backgroundColor: 'var(--color-bg)', borderRadius: '8px', border: '1px solid var(--color-border)', fontFamily: 'monospace' }}>
        <div style={{ textAlign: 'center', marginBottom: '16px', borderBottom: '1px dashed var(--color-border)', paddingBottom: '12px' }}>
          <h3 style={{ margin: 0, fontFamily: 'var(--font-sans)', fontSize: '1.2rem' }}>{accountInfo.businessName}</h3>
          <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{accountInfo.ownerName} • {accountInfo.phone}</span>
          <br />
          <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>Date: {order.date}</span>
        </div>

        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '12px', fontSize: '0.85rem' }}>
          <span>Customer: <strong>{order.customerName}</strong></span>
          <StatusPill status={order.status} />
        </div>

        <table style={{ width: '100%', fontSize: '0.85rem', marginBottom: '12px', borderCollapse: 'collapse' }}>
          <thead>
            <tr style={{ borderBottom: '1px solid var(--color-border)', textAlign: 'left' }}>
              <th style={{ padding: '4px 0' }}>Item</th>
              <th style={{ textAlign: 'center', padding: '4px 0' }}>Qty</th>
              <th style={{ textAlign: 'right', padding: '4px 0' }}>Price</th>
            </tr>
          </thead>
          <tbody>
            {order.items.map((item, idx) => (
              <tr key={idx} style={{ borderBottom: '1px dashed var(--color-border)' }}>
                <td style={{ padding: '6px 0' }}>{item.productName}{item.variantName && <small style={{ display: 'block' }}>{item.variantName}</small>}</td>
                <td style={{ textAlign: 'center', padding: '6px 0' }}>{item.quantity}</td>
                <td style={{ textAlign: 'right', padding: '6px 0' }}>{money(item.unitPrice * item.quantity, 2, order.currencyCode)}</td>
              </tr>
            ))}
          </tbody>
        </table>

        <div style={{ borderTop: '1px solid var(--color-border)', paddingTop: '8px', fontSize: '0.9rem' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
            <span>Subtotal:</span>
            <span>{money(order.subtotal, 2, order.currencyCode)}</span>
          </div>
          {order.discount > 0 && (
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px', color: 'var(--dorzak-success)' }}>
              <span>Discount:</span>
              <span>-{money(order.discount, 2, order.currencyCode)}</span>
            </div>
          )}
          <div style={{ display: 'flex', justifyContent: 'space-between', fontWeight: 700, fontSize: '1.05rem', marginTop: '6px', paddingTop: '6px', borderTop: '1px dashed var(--color-border)' }}>
            <span>TOTAL PAID ({order.paymentMethod}):</span>
            <span style={{ color: 'var(--dorzak-primary)' }}>{money(order.total, 2, order.currencyCode)}</span>
          </div>
        </div>

        <div style={{ textAlign: 'center', marginTop: '16px', fontSize: '0.75rem', color: 'var(--text-muted)' }}>
          Thank you for shopping with {accountInfo.businessName}!
        </div>
      </div>
    </BaseModal>
  );
};
