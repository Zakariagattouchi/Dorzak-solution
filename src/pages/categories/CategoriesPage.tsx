import React from 'react';
import { useProductStore } from '../../stores/productStore';
import { useModalStore } from '../../stores/modalStore';
import { DataTable, Column } from '../../components/tables/DataTable';
import { AppButton } from '../../components/buttons/AppButton';
import { Category } from '../../data/mockData';
import { AppIcon } from '../../components/icons/AppIcon';

export const CategoriesPage: React.FC = () => {
  const { categories } = useProductStore();
  const { openModal } = useModalStore();

  const columns: Column<Category>[] = [
    {
      key: 'name',
      header: 'Category Name',
      render: (row) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
          <div className="category-table-image" style={{ backgroundColor: row.color }}>{row.imageUrl ? <img src={row.imageUrl} alt="" /> : <AppIcon name="categories" />}</div>
          <strong>{row.name}</strong>
        </div>
      )
    },
    {
      key: 'productCount',
      header: 'Assigned Products',
      render: (row) => <span>{row.productCount} items</span>
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (row) => <AppButton variant="icon" aria-label={`Edit ${row.name}`} onClick={() => openModal('CATEGORY_EDIT', { category: row })}><AppIcon name="edit" /></AppButton>
    }
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h2 style={{ margin: 0 }}>Product Categories</h2>
          <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>Organize products for POS and online store display</span>
        </div>
        <AppButton icon="plus" variant="primary" onClick={() => openModal('CATEGORY_CREATE')}>
          New Category
        </AppButton>
      </div>

      <DataTable
        columns={columns}
        data={categories}
        keyExtractor={(c) => c.id}
      />
    </div>
  );
};
