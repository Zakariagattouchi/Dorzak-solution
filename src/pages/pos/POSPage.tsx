import React, { useState } from 'react';
import { useMoney } from '../../hooks/useMoney';
import { useProductStore } from '../../stores/productStore';
import { useCustomerStore } from '../../stores/customerStore';
import { useCartStore } from '../../stores/cartStore';
import { useModalStore } from '../../stores/modalStore';
import { useToastStore } from '../../stores/toastStore';
import { AppIcon } from '../../components/icons/AppIcon';
import { AppButton } from '../../components/buttons/AppButton';
import { TextInput } from '../../components/forms/TextInput';
import { useSettingsStore } from '../../stores/settingsStore';

export const POSPage: React.FC = () => {
  const money = useMoney();
  const { products, categories } = useProductStore();
  const { customers } = useCustomerStore();
  const { items, selectedCustomer, addItem, removeItem, updateQuantity, setSelectedCustomer, getSubtotal, getTotal } = useCartStore();
  const settings = useSettingsStore((state) => state.accountInfo);
  const { openModal } = useModalStore();
  const { addToast } = useToastStore();

  const [selectedCategory, setSelectedCategory] = useState<string>('ALL');
  const [searchQuery, setSearchQuery] = useState('');

  const filteredProducts = products.filter(p => {
    const matchesCategory = selectedCategory === 'ALL' || p.category === selectedCategory;
    const matchesSearch = p.name.toLowerCase().includes(searchQuery.toLowerCase()) || p.code.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCategory && matchesSearch;
  });

  const subtotal = getSubtotal();
  const preTaxTotal = getTotal();
  const taxAmount = settings.chargeSalesTax && !settings.taxIncludedInPrice
    ? Number((preTaxTotal * settings.taxRate / 100).toFixed(2))
    : 0;
  const total = preTaxTotal + taxAmount;

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 380px', gap: '24px', height: 'calc(100vh - 112px)' }}>
      {/* Left: Product POS Grid */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '16px', overflow: 'hidden' }}>
        <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
          <div style={{ flex: 1 }}>
            <TextInput
              placeholder="Search products by name or SKU..."
              icon="search"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
          </div>
          <AppButton icon="plus" variant="secondary" onClick={() => openModal('PRODUCT_CREATE')}>
            Add Product
          </AppButton>
        </div>

        {/* Category Filter Chips */}
        <div style={{ display: 'flex', gap: '8px', overflowX: 'auto', paddingBottom: '4px' }}>
          <button
            onClick={() => setSelectedCategory('ALL')}
            style={{
              padding: '6px 14px',
              borderRadius: '999px',
              border: '1px solid var(--color-border)',
              backgroundColor: selectedCategory === 'ALL' ? 'var(--dorzak-primary)' : 'var(--color-surface)',
              color: selectedCategory === 'ALL' ? '#fff' : 'var(--text-main)',
              fontSize: '0.85rem',
              fontWeight: 500,
              cursor: 'pointer',
              whiteSpace: 'nowrap'
            }}
          >
            All Items ({products.length})
          </button>
          {categories.map((c) => (
            <button
              key={c.id}
              onClick={() => setSelectedCategory(c.name)}
              style={{
                padding: '6px 14px',
                borderRadius: '999px',
                border: '1px solid var(--color-border)',
                backgroundColor: selectedCategory === c.name ? 'var(--dorzak-primary)' : 'var(--color-surface)',
                color: selectedCategory === c.name ? '#fff' : 'var(--text-main)',
                fontSize: '0.85rem',
                fontWeight: 500,
                cursor: 'pointer',
                whiteSpace: 'nowrap'
              }}
            >
              {c.name}
            </button>
          ))}
        </div>

        {/* Products Grid */}
        <div style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(180px, 1fr))',
          gap: '16px',
          overflowY: 'auto',
          paddingRight: '4px'
        }}>
          {filteredProducts.map((product) => (
            <button
              type="button"
              key={product.id}
              aria-label={`Choose ${product.name}`}
              onClick={() => {
                if (product.variants.length) {
                  openModal('VARIANT_SELECT', { product });
                } else {
                  addItem(product);
                  addToast(`Added "${product.name}" to cart`, 'info');
                }
              }}
              className="card"
              style={{
                padding: '12px',
                cursor: 'pointer',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                transition: 'transform 0.15s ease, box-shadow 0.15s ease',
                textAlign: 'left',
                width: '100%',
              }}
            >
              <div
                style={{
                  height: '110px',
                  borderRadius: '6px',
                  overflow: 'hidden',
                  backgroundColor: 'var(--color-bg)',
                  marginBottom: '8px',
                }}
              >
                <img
                  src={
                    product.imageUrl ||
                    'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=300'
                  }
                  alt={product.name}
                  style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                />
              </div>
              <div>
                <h5 style={{ margin: '0 0 4px 0', fontSize: '0.9rem', fontWeight: 600 }}>
                  {product.name}
                </h5>
                <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
                  SKU: {product.code}
                </span>
              </div>
              <div
                style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  marginTop: '8px',
                }}
              >
                <strong style={{ color: 'var(--dorzak-primary)', fontSize: '1rem' }}>
                  {money(product.price)}
                </strong>
                <span
                  style={{
                    fontSize: '0.75rem',
                    padding: '2px 6px',
                    borderRadius: '4px',
                    backgroundColor: 'var(--color-bg)',
                    color: 'var(--text-muted)',
                  }}
                >
                  Stock: {product.stock}
                </span>
              </div>
            </button>
          ))}
        </div>
      </div>

      {/* Right: Checkout Cart Panel */}
      <div className="card" style={{ display: 'flex', flexDirection: 'column', padding: '16px', height: '100%' }}>
        {/* Customer Attachment Header */}
        <div style={{ borderBottom: '1px solid var(--color-border)', paddingBottom: '12px', marginBottom: '12px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <span style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--text-muted)' }}>CUSTOMER</span>
            <button
              onClick={() => openModal('CUSTOMER_CREATE')}
              style={{ fontSize: '0.8rem', color: 'var(--dorzak-primary)', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '4px' }}
            >
              <AppIcon name="userPlus" size={14} /> New
            </button>
          </div>
          <select
            className="form-select"
            style={{ marginTop: '6px' }}
            value={selectedCustomer ? selectedCustomer.id : ''}
            onChange={(e) => {
              const cust = customers.find(c => c.id === e.target.value) || null;
              setSelectedCustomer(cust);
            }}
          >
            <option value="">Walk-in Customer</option>
            {customers.map(c => (
              <option key={c.id} value={c.id}>{c.name} ({c.phone})</option>
            ))}
          </select>
        </div>

        {/* Cart Item Rows */}
        <div style={{ flex: 1, overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '12px' }}>
          {items.length === 0 ? (
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', height: '100%', color: 'var(--text-muted)' }}>
              <AppIcon name="orders" size={48} />
              <p style={{ marginTop: '12px', fontSize: '0.9rem' }}>Cart is empty. Tap items to add.</p>
            </div>
          ) : (
            items.map((item) => (
              <div key={item.lineKey} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', paddingBottom: '10px', borderBottom: '1px dashed var(--color-border)' }}>
                <div style={{ flex: 1 }}>
                  <strong style={{ fontSize: '0.85rem', display: 'block' }}>{item.product.name}</strong>
                  {item.variant && <span style={{ fontSize: '0.75rem', color: 'var(--dorzak-primary)', display: 'block' }}>{item.variant.name}</span>}
                  <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>{money(item.variant?.price ?? item.product.price)} each</span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <button onClick={() => updateQuantity(item.lineKey, item.quantity - 1)} style={{ width: '24px', height: '24px', borderRadius: '4px', backgroundColor: 'var(--color-bg)', border: '1px solid var(--color-border)' }}>-</button>
                  <span style={{ fontWeight: 600, fontSize: '0.85rem' }}>{item.quantity}</span>
                  <button onClick={() => updateQuantity(item.lineKey, item.quantity + 1)} style={{ width: '24px', height: '24px', borderRadius: '4px', backgroundColor: 'var(--color-bg)', border: '1px solid var(--color-border)' }}>+</button>
                </div>
                <strong style={{ width: '72px', textAlign: 'end', fontSize: '0.9rem' }}>{money((item.variant?.price ?? item.product.price) * item.quantity)}</strong>
                <button onClick={() => removeItem(item.lineKey)} style={{ color: 'var(--dorzak-danger)', marginLeft: '8px' }}>
                  <AppIcon name="trash" size={14} />
                </button>
              </div>
            ))
          )}
        </div>

        {/* Footer Summary & Payment Button */}
        <div style={{ borderTop: '1px solid var(--color-border)', paddingTop: '12px', marginTop: '12px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.9rem', marginBottom: '4px' }}>
            <span>Subtotal:</span>
            <strong>{money(subtotal)}</strong>
          </div>
          {taxAmount > 0 && <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '0.85rem', color: 'var(--text-muted)' }}><span>Tax:</span><strong>{money(taxAmount)}</strong></div>}
          <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '1.1rem', fontWeight: 700, margin: '8px 0 16px 0', color: 'var(--dorzak-primary)' }}>
            <span>Total:</span>
            <span>{money(total)}</span>
          </div>

          <AppButton
            variant="primary"
            style={{ width: '100%', padding: '12px', fontSize: '1rem' }}
            disabled={items.length === 0}
            onClick={() => openModal('PAYMENT')}
          >
            Charge {money(total)}
          </AppButton>
        </div>
      </div>
    </div>
  );
};
