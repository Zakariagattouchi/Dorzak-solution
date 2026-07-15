import React, { useState } from 'react';
import { BaseModal } from './BaseModal';
import { AppButton } from '../buttons/AppButton';
import { useCartStore } from '../../stores/cartStore';
import { useOrderStore } from '../../stores/orderStore';
import { useProductStore } from '../../stores/productStore';
import { useToastStore } from '../../stores/toastStore';
import { useModalStore } from '../../stores/modalStore';
import { AppIcon } from '../icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';
import { useSettingsStore } from '../../stores/settingsStore';
import {
  captureMerchantScope,
  isMerchantScopeCurrent,
  requireCurrentMerchantScope,
} from '../../stores/merchantScope';

export const PaymentModal: React.FC = () => {
  const { activeModal, closeModal, openModal } = useModalStore();
  const { items, selectedCustomer, getSubtotal, getTotal, discount, clearCart } = useCartStore();
  const { createOrder } = useOrderStore();
  const { addToast } = useToastStore();
  const money = useMoney();

  const [paymentMethod, setPaymentMethod] = useState<'CASH' | 'CARD' | 'TRANSFER'>('CARD');
  const [loading, setLoading] = useState(false);

  const subtotal = getSubtotal();
  const preTaxTotal = getTotal();
  const settings = useSettingsStore((state) => state.accountInfo);
  const taxAmount =
    settings.chargeSalesTax && !settings.taxIncludedInPrice
      ? Number(((preTaxTotal * settings.taxRate) / 100).toFixed(2))
      : 0;
  const total = preTaxTotal + taxAmount;

  const handleCompleteSale = async () => {
    if (items.length === 0) return;
    const scope = captureMerchantScope();
    setLoading(true);
    try {
      requireCurrentMerchantScope(scope);
      const newOrder = await createOrder(
        {
          customerName: selectedCustomer ? selectedCustomer.name : 'Walk-in Customer',
          customerPhone: selectedCustomer ? selectedCustomer.phone : undefined,
          items: items.map((i) => ({
            productId: i.product.id,
            productName: i.product.name,
            quantity: i.quantity,
            unitPrice: i.variant?.price ?? i.product.price,
            variantId: i.variant?.id,
            variantName: i.variant?.name,
          })),
          subtotal,
          discount,
          taxAmount,
          total,
          status: 'COMPLETE',
          paymentStatus: 'PAID',
          paymentMethod,
          currencyCode: settings.currency,
        },
        selectedCustomer ? selectedCustomer.id : null,
      );
      requireCurrentMerchantScope(scope);

      addToast(`Sale ${newOrder.id} Completed! (${money(newOrder.total)})`, 'success');
      clearCart();
      openModal('PAYMENT_SUCCESS', { order: newOrder });
      // Stock changed on the server — refresh the catalog.
      useProductStore.getState().fetchProducts();
    } catch (err) {
      if (!isMerchantScopeCurrent(scope)) return;
      addToast('Failed to process sale', 'danger');
    } finally {
      if (isMerchantScopeCurrent(scope)) setLoading(false);
    }
  };

  return (
    <BaseModal
      isOpen={activeModal === 'PAYMENT'}
      onClose={closeModal}
      title="Complete Sale & Payment"
      footer={
        <>
          <AppButton variant="secondary" onClick={closeModal}>
            Cancel
          </AppButton>
          <AppButton variant="primary" onClick={handleCompleteSale} loading={loading}>
            Complete Sale ({money(total)})
          </AppButton>
        </>
      }
    >
      <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
        <div
          className="card"
          style={{
            padding: '16px',
            backgroundColor: 'var(--dorzak-primary-light)',
            borderColor: 'var(--dorzak-primary)',
          }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
            <span style={{ color: 'var(--text-muted)' }}>Customer:</span>
            <strong style={{ color: 'var(--text-main)' }}>
              {selectedCustomer ? selectedCustomer.name : 'Walk-in Customer'}
            </strong>
          </div>
          <div
            style={{
              display: 'flex',
              justifyContent: 'space-between',
              fontSize: '1.2rem',
              fontWeight: 700,
            }}
          >
            <span>Total Payable:</span>
            <span style={{ color: 'var(--dorzak-primary)' }}>{money(total)}</span>
          </div>
        </div>

        <div>
          <label className="form-label" style={{ marginBottom: '8px', display: 'block' }}>
            Select Payment Method
          </label>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px' }}>
            <button
              type="button"
              onClick={() => setPaymentMethod('CARD')}
              style={{
                padding: '16px 12px',
                borderRadius: '8px',
                border: `2px solid ${paymentMethod === 'CARD' ? 'var(--dorzak-primary)' : 'var(--color-border)'}`,
                backgroundColor:
                  paymentMethod === 'CARD' ? 'var(--dorzak-primary-light)' : 'var(--color-surface)',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                gap: '8px',
                fontWeight: 600,
                color: paymentMethod === 'CARD' ? 'var(--dorzak-primary)' : 'var(--text-main)',
              }}
            >
              <AppIcon name="billing" size={24} />
              <span>Card / POS</span>
            </button>

            <button
              type="button"
              onClick={() => setPaymentMethod('CASH')}
              style={{
                padding: '16px 12px',
                borderRadius: '8px',
                border: `2px solid ${paymentMethod === 'CASH' ? 'var(--dorzak-primary)' : 'var(--color-border)'}`,
                backgroundColor:
                  paymentMethod === 'CASH' ? 'var(--dorzak-primary-light)' : 'var(--color-surface)',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                gap: '8px',
                fontWeight: 600,
                color: paymentMethod === 'CASH' ? 'var(--dorzak-primary)' : 'var(--text-main)',
              }}
            >
              <AppIcon name="dollar" size={24} />
              <span>Cash</span>
            </button>

            <button
              type="button"
              onClick={() => setPaymentMethod('TRANSFER')}
              style={{
                padding: '16px 12px',
                borderRadius: '8px',
                border: `2px solid ${paymentMethod === 'TRANSFER' ? 'var(--dorzak-primary)' : 'var(--color-border)'}`,
                backgroundColor:
                  paymentMethod === 'TRANSFER'
                    ? 'var(--dorzak-primary-light)'
                    : 'var(--color-surface)',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                gap: '8px',
                fontWeight: 600,
                color: paymentMethod === 'TRANSFER' ? 'var(--dorzak-primary)' : 'var(--text-main)',
              }}
            >
              <AppIcon name="storefront" size={24} />
              <span>Transfer</span>
            </button>
          </div>
        </div>
      </div>
    </BaseModal>
  );
};
