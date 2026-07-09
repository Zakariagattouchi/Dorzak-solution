import React from 'react';
import { BaseModal } from './BaseModal';
import { AppButton } from '../buttons/AppButton';
import { AppIcon } from '../icons/AppIcon';
import { useModalStore } from '../../stores/modalStore';
import { Order } from '../../data/mockData';
import { useMoney } from '../../hooks/useMoney';

export const PaymentSuccessModal: React.FC = () => {
  const { activeModal, payload, closeModal, openModal } = useModalStore();
  const money = useMoney();
  const order: Order | undefined = payload?.order;
  if (!order) return null;

  return (
    <BaseModal
      isOpen={activeModal === 'PAYMENT_SUCCESS'}
      onClose={closeModal}
      title="Payment Confirmed"
      footer={<>
        <AppButton variant="secondary" onClick={closeModal}>New Sale</AppButton>
        <AppButton variant="primary" icon="receipt" onClick={() => openModal('RECEIPT', { order })}>View & Print Receipt</AppButton>
      </>}
    >
      <div className="payment-success">
        <div className="success-mark"><AppIcon name="check" size={34} /></div>
        <h3>{money(order.total, 2, order.currencyCode)} paid successfully</h3>
        <p>Order <strong>{order.id}</strong> is complete. The receipt is ready to print.</p>
        <div className="receipt-ready">
          <span>{order.items.reduce((sum, item) => sum + item.quantity, 0)} items</span>
          <span>{order.paymentMethod}</span>
          <strong>{money(order.total, 2, order.currencyCode)}</strong>
        </div>
      </div>
    </BaseModal>
  );
};
