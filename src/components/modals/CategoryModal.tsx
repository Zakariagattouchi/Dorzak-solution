import React, { useEffect, useMemo, useState } from 'react';
import { BaseModal } from './BaseModal';
import { TextInput } from '../forms/TextInput';
import { AppButton } from '../buttons/AppButton';
import { useProductStore } from '../../stores/productStore';
import { useToastStore } from '../../stores/toastStore';
import { useModalStore } from '../../stores/modalStore';

export const CategoryModal: React.FC = () => {
  const { activeModal, payload, closeModal } = useModalStore();
  const { addCategory, updateCategory } = useProductStore();
  const { addToast } = useToastStore();

  const [name, setName] = useState('');
  const [color, setColor] = useState('#3b82f6');
  const [description, setDescription] = useState('');
  const [imageFile, setImageFile] = useState<File | null>(null);
  const editing = activeModal === 'CATEGORY_EDIT';
  const category = payload?.category;
  const preview = useMemo(
    () => (imageFile ? URL.createObjectURL(imageFile) : category?.imageUrl),
    [imageFile, category?.imageUrl],
  );

  useEffect(() => {
    if (editing && category) {
      setName(category.name);
      setColor(category.color);
      setDescription(category.description ?? '');
      setImageFile(null);
    } else if (activeModal === 'CATEGORY_CREATE') {
      setName('');
      setColor('#3b82f6');
      setDescription('');
      setImageFile(null);
    }
  }, [activeModal, editing, category]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name) {
      addToast('Please enter category name', 'warning');
      return;
    }
    if (!editing && !imageFile) {
      addToast('Please choose a category photo', 'warning');
      return;
    }
    if (editing) await updateCategory(category.id, name, color, imageFile, description);
    else await addCategory(name, color, imageFile, description);
    addToast(`Category "${name}" ${editing ? 'updated' : 'created'}!`, 'success');
    closeModal();
    setName('');
    setDescription('');
  };

  return (
    <BaseModal
      isOpen={activeModal === 'CATEGORY_CREATE' || activeModal === 'CATEGORY_EDIT'}
      onClose={closeModal}
      title={editing ? 'Edit Category' : 'Create Category'}
      footer={
        <>
          <AppButton variant="secondary" onClick={closeModal}>
            Cancel
          </AppButton>
          <AppButton variant="primary" onClick={handleSubmit}>
            Save Category
          </AppButton>
        </>
      }
    >
      <form onSubmit={handleSubmit}>
        <TextInput
          label="Category Name *"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="e.g. Footwear"
        />
        <div className="form-group">
          <label className="form-label">Description</label>
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="Describe the category products..."
            style={{
              width: '100%',
              minHeight: '80px',
              padding: '8px 12px',
              border: '1px solid #d1d5db',
              borderRadius: '6px',
              fontSize: '0.9rem',
              outline: 'none',
            }}
          />
        </div>
        <label className="category-image-picker">
          <span className="form-label">Category Photo *</span>
          <div>
            {preview ? (
              <img src={preview} alt="Category preview" />
            ) : (
              <span>Choose a category photo</span>
            )}
          </div>
          <input
            type="file"
            accept="image/jpeg,image/png,image/webp"
            onChange={(event) => setImageFile(event.target.files?.[0] ?? null)}
          />
        </label>
        <div className="form-group">
          <label className="form-label">Accent Color</label>
          <input
            type="color"
            value={color}
            onChange={(e) => setColor(e.target.value)}
            style={{
              width: '60px',
              height: '36px',
              padding: '0',
              borderRadius: '4px',
              cursor: 'pointer',
            }}
          />
        </div>
      </form>
    </BaseModal>
  );
};
