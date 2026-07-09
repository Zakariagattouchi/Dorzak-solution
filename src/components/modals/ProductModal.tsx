import React, { useState, useEffect } from 'react';
import { BaseModal } from './BaseModal';
import { TextInput } from '../forms/TextInput';
import { SelectInput } from '../forms/SelectInput';
import { ToggleSwitch } from '../forms/ToggleSwitch';
import { CheckboxInput } from '../forms/CheckboxInput';
import { AppButton } from '../buttons/AppButton';
import { useProductStore } from '../../stores/productStore';
import { useToastStore } from '../../stores/toastStore';
import { useModalStore } from '../../stores/modalStore';
import { ProductVariant } from '../../data/mockData';
import { AppIcon } from '../icons/AppIcon';

export const ProductModal: React.FC = () => {
  const { activeModal, payload, closeModal } = useModalStore();
  const { categories, addProduct } = useProductStore();
  const { addToast } = useToastStore();

  const [activeTab, setActiveTab] = useState<'BASIC' | 'PRICING' | 'STOCK' | 'VARIANTS' | 'ONLINE'>('BASIC');

  // Form Fields
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [category, setCategory] = useState('');
  const [code, setCode] = useState('');
  const [unit, setUnit] = useState('pcs');
  const [imageUrl, setImageUrl] = useState('');

  const [price, setPrice] = useState('');
  const [cost, setCost] = useState('');
  const [taxable, setTaxable] = useState(true);

  const [stock, setStock] = useState('10');
  const [minStock, setMinStock] = useState('5');
  const [trackStock, setTrackStock] = useState(true);

  const [showInOnlineStore, setShowInOnlineStore] = useState(true);
  const [isFeatured, setIsFeatured] = useState(false);

  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [newVarName, setNewVarName] = useState('');
  const [newVarPrice, setNewVarPrice] = useState('');
  const [newVarStock, setNewVarStock] = useState('10');

  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (categories.length > 0 && !category) {
      setCategory(categories[0].name);
    }
  }, [categories]);

  const addVariant = () => {
    if (!newVarName) return;
    const v: ProductVariant = {
      id: `v_${Date.now()}`,
      name: newVarName,
      optionValues: {},
      price: parseFloat(newVarPrice) || parseFloat(price) || 0,
      stock: parseInt(newVarStock) || 0,
      code: `${code || 'SKU'}-${newVarName.replace(/\s+/g, '-').toUpperCase()}`,
      isActive: true,
    };
    setVariants([...variants, v]);
    setNewVarName('');
    setNewVarPrice('');
  };

  const removeVariant = (id: string) => {
    setVariants(variants.filter(v => v.id !== id));
  };

  const sellingPrice = parseFloat(price) || 0;
  const costPrice = parseFloat(cost) || 0;
  const profitMargin = sellingPrice - costPrice;
  const profitPercentage = sellingPrice > 0 ? ((profitMargin / sellingPrice) * 100).toFixed(1) : '0';

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name || !price) {
      addToast('Please enter product name and selling price', 'warning');
      setActiveTab('BASIC');
      return;
    }
    setLoading(true);
    try {
      await addProduct({
        name,
        description,
        price: sellingPrice,
        cost: costPrice,
        stock: parseInt(stock) || 0,
        minStock: parseInt(minStock) || 0,
        trackStock,
        category: category || categories[0]?.name || 'General',
        code: code || `PROD-${Math.floor(100 + Math.random() * 900)}`,
        unit,
        imageUrl: imageUrl || 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=500',
        taxable,
        showInOnlineStore,
        isFeatured,
        variants,
        variantGroups: []
      });
      addToast(`Product "${name}" created with ${variants.length} variants!`, 'success');
      closeModal();
      // Reset form
      setName('');
      setDescription('');
      setPrice('');
      setCost('');
      setVariants([]);
      setActiveTab('BASIC');
    } catch (err) {
      addToast('Failed to create product', 'danger');
    } finally {
      setLoading(false);
    }
  };

  return (
    <BaseModal
      isOpen={activeModal === 'PRODUCT_CREATE'}
      onClose={closeModal}
      title="Create Production Product"
      footer={
        <>
          <AppButton variant="secondary" onClick={closeModal}>Cancel</AppButton>
          <AppButton variant="primary" onClick={handleSubmit} loading={loading}>Save Product</AppButton>
        </>
      }
    >
      {/* Navigation Sub-Tabs */}
      <div style={{ display: 'flex', gap: '4px', borderBottom: '1px solid var(--color-border)', marginBottom: '16px', overflowX: 'auto' }}>
        <button
          type="button"
          onClick={() => setActiveTab('BASIC')}
          style={{
            padding: '8px 12px',
            fontSize: '0.85rem',
            fontWeight: 600,
            borderBottom: activeTab === 'BASIC' ? '2px solid var(--dorzak-primary)' : '2px solid transparent',
            color: activeTab === 'BASIC' ? 'var(--dorzak-primary)' : 'var(--text-muted)'
          }}
        >
          Basic Info
        </button>
        <button
          type="button"
          onClick={() => setActiveTab('PRICING')}
          style={{
            padding: '8px 12px',
            fontSize: '0.85rem',
            fontWeight: 600,
            borderBottom: activeTab === 'PRICING' ? '2px solid var(--dorzak-primary)' : '2px solid transparent',
            color: activeTab === 'PRICING' ? 'var(--dorzak-primary)' : 'var(--text-muted)'
          }}
        >
          Pricing & Profit
        </button>
        <button
          type="button"
          onClick={() => setActiveTab('STOCK')}
          style={{
            padding: '8px 12px',
            fontSize: '0.85rem',
            fontWeight: 600,
            borderBottom: activeTab === 'STOCK' ? '2px solid var(--dorzak-primary)' : '2px solid transparent',
            color: activeTab === 'STOCK' ? 'var(--dorzak-primary)' : 'var(--text-muted)'
          }}
        >
          Stock & Alerts
        </button>
        <button
          type="button"
          onClick={() => setActiveTab('VARIANTS')}
          style={{
            padding: '8px 12px',
            fontSize: '0.85rem',
            fontWeight: 600,
            borderBottom: activeTab === 'VARIANTS' ? '2px solid var(--dorzak-primary)' : '2px solid transparent',
            color: activeTab === 'VARIANTS' ? 'var(--dorzak-primary)' : 'var(--text-muted)'
          }}
        >
          Variants ({variants.length})
        </button>
        <button
          type="button"
          onClick={() => setActiveTab('ONLINE')}
          style={{
            padding: '8px 12px',
            fontSize: '0.85rem',
            fontWeight: 600,
            borderBottom: activeTab === 'ONLINE' ? '2px solid var(--dorzak-primary)' : '2px solid transparent',
            color: activeTab === 'ONLINE' ? 'var(--dorzak-primary)' : 'var(--text-muted)'
          }}
        >
          Online Store
        </button>
      </div>

      <form onSubmit={handleSubmit}>
        {/* Tab 1: Basic Info */}
        {activeTab === 'BASIC' && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
            <TextInput label="Product Title *" value={name} onChange={(e) => setName(e.target.value)} placeholder="e.g. Premium Fleece Hoodie" />
            
            <div className="form-group">
              <label className="form-label">Product Description</label>
              <textarea
                className="form-textarea"
                rows={3}
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                placeholder="Detailed description for receipt and online storefront catalog..."
              />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
              <SelectInput
                label="Category"
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                options={categories.map(c => ({ value: c.name, label: c.name }))}
              />
              <SelectInput
                label="Unit of Measure"
                value={unit}
                onChange={(e) => setUnit(e.target.value)}
                options={[
                  { value: 'pcs', label: 'Pieces (pcs)' },
                  { value: 'kg', label: 'Kilograms (kg)' },
                  { value: 'box', label: 'Box / Pack' },
                  { value: 'm', label: 'Meters (m)' },
                  { value: 'bottle', label: 'Bottle / Can' }
                ]}
              />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
              <TextInput label="SKU / Barcode" value={code} onChange={(e) => setCode(e.target.value)} placeholder="e.g. HD-9901" />
              <TextInput label="Image URL" value={imageUrl} onChange={(e) => setImageUrl(e.target.value)} placeholder="https://..." />
            </div>
          </div>
        )}

        {/* Tab 2: Pricing & Profit */}
        {activeTab === 'PRICING' && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
              <TextInput label="Selling Price ($) *" type="number" step="0.01" value={price} onChange={(e) => setPrice(e.target.value)} placeholder="0.00" />
              <TextInput label="Cost Price ($)" type="number" step="0.01" value={cost} onChange={(e) => setCost(e.target.value)} placeholder="0.00" />
            </div>

            {/* Calculated Profit Indicator */}
            <div className="card" style={{ padding: '12px', backgroundColor: 'var(--dorzak-success-light)', borderColor: 'var(--dorzak-success)' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--dorzak-success)' }}>ESTIMATED PROFIT MARGIN</span>
                <strong style={{ fontSize: '1.1rem', color: 'var(--dorzak-success)' }}>
                  ${profitMargin.toFixed(2)} ({profitPercentage}%)
                </strong>
              </div>
            </div>

            <CheckboxInput
              checked={taxable}
              onChange={setTaxable}
              label="Item is Subject to Sales Tax"
            />
          </div>
        )}

        {/* Tab 3: Stock & Alerts */}
        {activeTab === 'STOCK' && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <ToggleSwitch
              checked={trackStock}
              onChange={setTrackStock}
              label="Track Inventory Stock Quantity"
              description="Automatically decrease stock levels when POS sales are completed"
            />

            {trackStock && (
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <TextInput label="Current Stock Quantity" type="number" value={stock} onChange={(e) => setStock(e.target.value)} />
                <TextInput label="Minimum Stock Alert Level" type="number" value={minStock} onChange={(e) => setMinStock(e.target.value)} />
              </div>
            )}
          </div>
        )}

        {/* Tab 4: Variants & Options */}
        {activeTab === 'VARIANTS' && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <div style={{ backgroundColor: 'var(--color-bg)', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-border)' }}>
              <span style={{ fontSize: '0.85rem', fontWeight: 600, display: 'block', marginBottom: '8px' }}>Add Product Variant</span>
              <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr auto', gap: '8px', alignItems: 'end' }}>
                <TextInput placeholder="Variant (e.g. Size M / Blue)" value={newVarName} onChange={(e) => setNewVarName(e.target.value)} />
                <TextInput placeholder="Price ($)" type="number" step="0.01" value={newVarPrice} onChange={(e) => setNewVarPrice(e.target.value)} />
                <TextInput placeholder="Stock" type="number" value={newVarStock} onChange={(e) => setNewVarStock(e.target.value)} />
                <AppButton variant="secondary" type="button" onClick={addVariant}>+ Add</AppButton>
              </div>
            </div>

            {/* List of Added Variants */}
            {variants.length === 0 ? (
              <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)', textAlign: 'center', display: 'block', padding: '12px' }}>
                No variants added yet. Add sizes, colors, or options above.
              </span>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {variants.map((v) => (
                  <div key={v.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '8px 12px', backgroundColor: 'var(--color-surface)', border: '1px solid var(--color-border)', borderRadius: '6px' }}>
                    <div>
                      <strong>{v.name}</strong>
                      <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)', marginLeft: '8px' }}>SKU: {v.code}</span>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                      <span style={{ color: 'var(--dorzak-primary)', fontWeight: 600 }}>${v.price.toFixed(2)}</span>
                      <span style={{ fontSize: '0.8rem' }}>Stock: {v.stock}</span>
                      <button type="button" onClick={() => removeVariant(v.id)} style={{ color: 'var(--dorzak-danger)' }}>
                        <AppIcon name="close" size={14} />
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* Tab 5: Online Store */}
        {activeTab === 'ONLINE' && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <ToggleSwitch
              checked={showInOnlineStore}
              onChange={setShowInOnlineStore}
              label="Display in Online Store Catalog"
              description="Make this product visible for public customer browsing and web orders"
            />

            <ToggleSwitch
              checked={isFeatured}
              onChange={setIsFeatured}
              label="Mark as Featured Product"
              description="Highlight this item at the top of your public online storefront"
            />
          </div>
        )}
      </form>
    </BaseModal>
  );
};
