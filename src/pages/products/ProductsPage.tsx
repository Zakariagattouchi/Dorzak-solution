import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useProductStore } from '../../stores/productStore';
import { useToastStore } from '../../stores/toastStore';
import { DataTable, Column } from '../../components/tables/DataTable';
import { AppButton } from '../../components/buttons/AppButton';
import { TextInput } from '../../components/forms/TextInput';
import { Product } from '../../data/mockData';
import { AppIcon } from '../../components/icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';

export const ProductsPage: React.FC = () => {
  const navigate = useNavigate();
  const { products, deleteProduct } = useProductStore();
  const { addToast } = useToastStore();
  const money = useMoney();
  const [search, setSearch] = useState('');

  const filtered = products.filter(
    (p) =>
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      p.code.toLowerCase().includes(search.toLowerCase()) ||
      p.category.toLowerCase().includes(search.toLowerCase()),
  );

  const columns: Column<Product>[] = [
    {
      key: 'name',
      header: 'Product',
      render: (row) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
          <img
            src={row.imageUrl || 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=300'}
            alt={row.name}
            style={{ width: '36px', height: '36px', borderRadius: '6px', objectFit: 'cover' }}
          />
          <div>
            <strong style={{ display: 'block', fontSize: '0.9rem' }}>{row.name}</strong>
            <span style={{ fontSize: '0.75rem', color: 'var(--text-muted)' }}>
              SKU: {row.code} {row.variants?.length ? `• ${row.variants.length} variants` : ''}
            </span>
          </div>
        </div>
      ),
    },
    { key: 'category', header: 'Category' },
    {
      key: 'price',
      header: 'Price',
      render: (row) => (
        <strong style={{ color: 'var(--dorzak-primary)' }}>{money(row.price)}</strong>
      ),
    },
    {
      key: 'stock',
      header: 'Stock Status',
      render: (row) => (
        <span
          className={`status-pill ${row.stock > (row.minStock || 10) ? 'success' : row.stock > 0 ? 'warning' : 'danger'}`}
        >
          {row.stock > 0 ? `${row.stock} in stock` : 'Out of stock'}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (row) => (
        <div className="table-actions">
          <AppButton
            variant="icon"
            aria-label={`Edit ${row.name}`}
            onClick={() => navigate(`/products/${row.id}/edit`)}
          >
            <AppIcon name="edit" size={16} />
          </AppButton>
          <AppButton
            variant="icon"
            onClick={async () => {
              await deleteProduct(row.id);
              addToast(`Product "${row.name}" deleted`, 'danger');
            }}
          >
            <AppIcon name="trash" size={16} color="var(--dorzak-danger)" />
          </AppButton>
        </div>
      ),
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h2 style={{ margin: 0 }}>Products Catalog</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
            Manage inventory items, pricing, variants, and stock levels
          </span>
        </div>
        <AppButton icon="plus" variant="primary" onClick={() => navigate('/products/create')}>
          Add a Product
        </AppButton>
      </div>

      <div style={{ display: 'flex', gap: '12px' }}>
        <div style={{ flex: 1 }}>
          <TextInput
            placeholder="Search catalog by name, category or SKU..."
            icon="search"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
      </div>

      <DataTable columns={columns} data={filtered} keyExtractor={(p) => p.id} />
    </div>
  );
};
