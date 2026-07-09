import React, { useEffect, useMemo, useState } from 'react';
import { BaseModal } from './BaseModal';
import { AppButton } from '../buttons/AppButton';
import { useModalStore } from '../../stores/modalStore';
import { useCartStore } from '../../stores/cartStore';
import { useToastStore } from '../../stores/toastStore';
import { Product } from '../../data/mockData';
import { useMoney } from '../../hooks/useMoney';

export const VariantSelectModal: React.FC = () => {
  const { activeModal, payload, closeModal } = useModalStore();
  const addItem = useCartStore((state) => state.addItem);
  const addToast = useToastStore((state) => state.addToast);
  const money = useMoney();
  const product: Product | undefined = payload?.product;
  const [selected, setSelected] = useState<Record<string, string>>({});

  useEffect(() => { if (activeModal === 'VARIANT_SELECT') setSelected({}); }, [activeModal, product?.id]);

  const variant = useMemo(() => product?.variants.find((item) => {
    if (!item.isActive) return false;
    return Object.entries(selected).every(([groupId, optionId]) => item.optionValues[groupId] === optionId)
      && Object.entries(item.optionValues).every(([groupId, optionId]) => selected[groupId] === optionId);
  }), [product, selected]);

  if (!product) return null;
  const missingRequired = product.variantGroups.some((group) => group.required && !selected[group.id]);

  return (
    <BaseModal
      isOpen={activeModal === 'VARIANT_SELECT'}
      onClose={closeModal}
      title={`Choose ${product.name} options`}
      footer={<>
        <AppButton variant="secondary" onClick={closeModal}>Cancel</AppButton>
        <AppButton variant="primary" disabled={missingRequired || !variant || variant.stock <= 0} onClick={() => {
          if (!variant) return;
          addItem(product, variant);
          addToast(`${product.name} — ${variant.name} added to cart`, 'success');
          closeModal();
        }}>Add to Cart {variant ? `• ${money(variant.price)}` : ''}</AppButton>
      </>}
    >
      <div className="variant-selector">
        {product.variantGroups.map((group) => (
          <fieldset key={group.id}>
            <legend>{group.name} {group.required ? <span>*</span> : <small>(Optional)</small>}</legend>
            <div className="variant-options">
              {!group.required && <button type="button" className={!selected[group.id] ? 'selected' : ''} onClick={() => setSelected((current) => {
                const copy = { ...current }; delete copy[group.id]; return copy;
              })}>None</button>}
              {group.options.map((option) => (
                <button type="button" key={option.id} className={selected[group.id] === option.id ? 'selected' : ''} onClick={() => setSelected((current) => ({ ...current, [group.id]: option.id }))}>{option.name}</button>
              ))}
            </div>
          </fieldset>
        ))}
        {!missingRequired && !variant && <p className="form-error">This combination is unavailable.</p>}
        {variant && variant.stock <= 0 && <p className="form-error">This combination is out of stock.</p>}
      </div>
    </BaseModal>
  );
};
