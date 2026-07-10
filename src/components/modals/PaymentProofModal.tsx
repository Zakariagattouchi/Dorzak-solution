import React, { useEffect, useState } from 'react';
import { BaseModal } from './BaseModal';
import { AppButton } from '../buttons/AppButton';
import { useModalStore } from '../../stores/modalStore';
import { orderApi } from '../../api/endpoints';

/**
 * Shows the customer's uploaded payment receipt inline. The image is fetched
 * as an authenticated blob (the proof endpoint needs the bearer token, so it
 * can't be a plain <img src>) and rendered here — opening it via window.open
 * after the fetch resolves is unreliable, browsers treat the delayed popup as
 * unsolicited and block it.
 */
export const PaymentProofModal: React.FC = () => {
  const { activeModal, payload, closeModal } = useModalStore();
  const isOpen = activeModal === 'PAYMENT_PROOF';
  const orderId: number | undefined = payload?.orderId;
  const orderLabel: string | undefined = payload?.orderLabel;

  const [url, setUrl] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!isOpen || !orderId) return;

    let objectUrl: string | null = null;
    let cancelled = false;
    setUrl(null);
    setError(null);

    orderApi
      .paymentProof(orderId)
      .then((blob) => {
        if (cancelled) return;
        objectUrl = URL.createObjectURL(blob);
        setUrl(objectUrl);
      })
      .catch((e: any) => {
        if (!cancelled) setError(e?.message ?? 'Payment proof is unavailable.');
      });

    return () => {
      cancelled = true;
      if (objectUrl) URL.revokeObjectURL(objectUrl);
    };
  }, [isOpen, orderId]);

  if (!isOpen) return null;

  return (
    <BaseModal
      isOpen={isOpen}
      onClose={closeModal}
      title={orderLabel ? `Payment proof · ${orderLabel}` : 'Payment proof'}
      footer={
        <>
          {url && (
            <a href={url} target="_blank" rel="noopener noreferrer" download style={{ textDecoration: 'none' }}>
              <AppButton variant="secondary" icon="eye">Open full size</AppButton>
            </a>
          )}
          <AppButton variant="secondary" onClick={closeModal}>Close</AppButton>
        </>
      }
    >
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '240px' }}>
        {error ? (
          <div style={{ color: 'var(--dorzak-danger, #d32f2f)', textAlign: 'center' }}>{error}</div>
        ) : !url ? (
          <div style={{ color: 'var(--text-muted)' }}>Loading proof…</div>
        ) : (
          <img src={url} alt="Payment receipt" style={{ maxWidth: '100%', maxHeight: '70vh', borderRadius: '8px' }} />
        )}
      </div>
    </BaseModal>
  );
};
