import React from 'react';
import { ProductModal } from './ProductModal';
import { CustomerModal } from './CustomerModal';
import { CategoryModal } from './CategoryModal';
import { PaymentModal } from './PaymentModal';
import { ReceiptModal } from './ReceiptModal';
import { VariantSelectModal } from './VariantSelectModal';
import { PaymentSuccessModal } from './PaymentSuccessModal';
import { OrderDetailModal } from './OrderDetailModal';
import { PaymentProofModal } from './PaymentProofModal';

export const ModalHost: React.FC = () => {
  return (
    <>
      <ProductModal />
      <CustomerModal />
      <CategoryModal />
      <PaymentModal />
      <VariantSelectModal />
      <PaymentSuccessModal />
      <ReceiptModal />
      <OrderDetailModal />
      <PaymentProofModal />
    </>
  );
};
