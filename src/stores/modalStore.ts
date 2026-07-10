import { create } from 'zustand';

export type ModalType = 'PRODUCT_CREATE' | 'PRODUCT_EDIT' | 'CUSTOMER_CREATE' | 'CATEGORY_CREATE' | 'CATEGORY_EDIT' | 'PAYMENT' | 'PAYMENT_SUCCESS' | 'VARIANT_SELECT' | 'RECEIPT' | 'ORDER_DETAIL' | 'PAYMENT_PROOF' | null;

interface ModalState {
  activeModal: ModalType;
  payload: any;
  openModal: (type: ModalType, payload?: any) => void;
  closeModal: () => void;
}

export const useModalStore = create<ModalState>((set) => ({
  activeModal: null,
  payload: null,
  openModal: (activeModal, payload = null) => set({ activeModal, payload }),
  closeModal: () => set({ activeModal: null, payload: null }),
}));
