import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useProductStore } from '../../stores/productStore';
import { useSettingsStore } from '../../stores/settingsStore';
import { useToastStore } from '../../stores/toastStore';
import { catalogApi } from '../../api/endpoints';
import { TextInput } from '../../components/forms/TextInput';
import { SelectInput } from '../../components/forms/SelectInput';
import { ToggleSwitch } from '../../components/forms/ToggleSwitch';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon } from '../../components/icons/AppIcon';
import { ProductVariationGroup, ProductVariant } from '../../data/mockData';

const uid = (prefix: string) => `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;

function buildCombinations(
  groups: ProductVariationGroup[],
  previous: ProductVariant[],
  basePrice: number,
): ProductVariant[] {
  if (!groups.length) return [];
  const choices = groups.map((group) => [
    ...(group.required ? [] : [{ id: '', name: 'None' }]),
    ...group.options,
  ]);
  const rows = choices.reduce<Array<Array<{ groupId: string; optionId: string; name: string }>>>(
    (result, options, index) =>
      result.flatMap((row) =>
        options.map((option) => [
          ...row,
          { groupId: groups[index].id, optionId: option.id, name: option.name },
        ]),
      ),
    [[]],
  );

  return rows.map((row) => {
    const optionValues = Object.fromEntries(
      row.filter((item) => item.optionId).map((item) => [item.groupId, item.optionId]),
    );
    const key = JSON.stringify(optionValues);
    const existing = previous.find((variant) => JSON.stringify(variant.optionValues) === key);
    const name =
      row
        .filter((item) => item.optionId)
        .map((item) => item.name)
        .join(' / ') || 'Standard';
    return (
      existing ?? {
        id: uid('new'),
        name,
        optionValues,
        price: basePrice,
        stock: 0,
        code: '',
        isActive: true,
      }
    );
  });
}

export const ProductCreatePage: React.FC = () => {
  const { id } = useParams();
  const editing = Boolean(id);
  const navigate = useNavigate();
  const { categories, getProduct, createProduct, updateProduct, fetchCategories } =
    useProductStore();
  const currency = useSettingsStore((state) => state.accountInfo.currency);
  const { addToast } = useToastStore();

  const [loading, setLoading] = useState(editing);
  const [saving, setSaving] = useState(false);
  const [translating, setTranslating] = useState(false);
  const [name, setName] = useState('');
  const [nameAr, setNameAr] = useState('');
  const [description, setDescription] = useState('');
  const [descriptionAr, setDescriptionAr] = useState('');
  const [price, setPrice] = useState('');
  const [cost, setCost] = useState('');
  const [category, setCategory] = useState('');
  const [productCode, setProductCode] = useState('');
  const [manageStock, setManageStock] = useState(true);
  const [stock, setStock] = useState('0');
  const [minStock, setMinStock] = useState('0');
  const [showOnline, setShowOnline] = useState(true);
  const [featured, setFeatured] = useState(false);
  const [imageUrl, setImageUrl] = useState<string>();
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [groups, setGroups] = useState<ProductVariationGroup[]>([]);
  const [variants, setVariants] = useState<ProductVariant[]>([]);
  const [additionalImages, setAdditionalImages] = useState<string[]>([]);
  const [additionalImagePaths, setAdditionalImagePaths] = useState<string[]>([]);
  const [additionalImageFiles, setAdditionalImageFiles] = useState<(File | null)[]>([
    null,
    null,
    null,
  ]);
  const [removePaths, setRemovePaths] = useState<string[]>([]);
  const [mainFocus, setMainFocus] = useState<string>('50% 50%');
  const [additionalFocus, setAdditionalFocus] = useState<string[]>([
    '50% 50%',
    '50% 50%',
    '50% 50%',
  ]);

  const handleFileChange = (file: File, fileIndex: number) => {
    setAdditionalImageFiles((prev) => {
      const next = [...prev];
      next[fileIndex] = file;
      return next;
    });
  };

  const handleImageClick = (
    event: React.MouseEvent<HTMLDivElement>,
    type: 'main' | 'additional',
    index: number = 0,
  ) => {
    const rect = event.currentTarget.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width) * 100;
    const y = ((event.clientY - rect.top) / rect.height) * 100;
    const coordString = `${x.toFixed(0)}% ${y.toFixed(0)}%`;

    if (type === 'main') {
      setMainFocus(coordString);
    } else {
      setAdditionalFocus((prev) => {
        const next = [...prev];
        next[index] = coordString;
        return next;
      });
    }
  };

  const handleRemoveSlot = (slotIdx: number, type: 'uploaded' | 'file') => {
    if (type === 'uploaded') {
      const pathToRemove = additionalImagePaths[slotIdx];
      if (pathToRemove) {
        setRemovePaths((prev) => [...prev, pathToRemove]);
      }
      setAdditionalImages((prev) => prev.filter((_, idx) => idx !== slotIdx));
      setAdditionalImagePaths((prev) => prev.filter((_, idx) => idx !== slotIdx));
      setAdditionalFocus((prev) => prev.filter((_, idx) => idx !== slotIdx));
    } else {
      const fileIdx = slotIdx - additionalImages.length;
      setAdditionalImageFiles((prev) => {
        const next = [...prev];
        next[fileIdx] = null;
        return next;
      });
      setAdditionalFocus((prev) => {
        const next = [...prev];
        next[slotIdx] = '50% 50%';
        return next;
      });
    }
  };

  useEffect(() => {
    if (!categories.length) fetchCategories();
  }, [categories.length, fetchCategories]);

  useEffect(() => {
    if (!id) return;
    setLoading(true);
    getProduct(id)
      .then((product) => {
        setName(product.name);
        setNameAr(product.nameAr ?? '');
        setDescription(product.description ?? '');
        setDescriptionAr(product.descriptionAr ?? '');
        setPrice(String(product.price));
        setCost(String(product.cost));
        setCategory(product.category);
        setProductCode(product.code);
        setManageStock(product.trackStock);
        setStock(String(product.stock));
        setMinStock(String(product.minStock));
        setShowOnline(product.showInOnlineStore);
        setFeatured(product.isFeatured);
        setImageUrl(product.imageUrl);
        setGroups(product.variantGroups);
        setVariants(product.variants);
        setAdditionalImages(product.additionalImages ?? []);
        setAdditionalImagePaths(product.additionalImagePaths ?? []);
        setRemovePaths([]);
        setAdditionalImageFiles([null, null, null]);
        setMainFocus(product.imageFocus?.main ?? '50% 50%');
        setAdditionalFocus(product.imageFocus?.additional ?? ['50% 50%', '50% 50%', '50% 50%']);
      })
      .catch(() => {
        addToast('Failed to load the selected product', 'danger');
        navigate('/products');
      })
      .finally(() => setLoading(false));
  }, [id, getProduct, navigate, addToast]);

  useEffect(() => {
    if (!category && categories[0]) setCategory(categories[0].name);
  }, [categories, category]);

  const preview = useMemo(
    () => (imageFile ? URL.createObjectURL(imageFile) : imageUrl),
    [imageFile, imageUrl],
  );

  const addGroup = () => {
    setGroups((current) => [
      ...current,
      {
        id: uid('group'),
        name: '',
        required: true,
        options: [{ id: uid('option'), name: '' }],
      },
    ]);
  };

  const updateGroup = (groupId: string, patch: Partial<ProductVariationGroup>) =>
    setGroups((current) =>
      current.map((group) => (group.id === groupId ? { ...group, ...patch } : group)),
    );

  const addOption = (groupId: string) =>
    setGroups((current) =>
      current.map((group) =>
        group.id === groupId
          ? { ...group, options: [...group.options, { id: uid('option'), name: '' }] }
          : group,
      ),
    );

  const updateOption = (groupId: string, optionId: string, nameValue: string) =>
    setGroups((current) =>
      current.map((group) =>
        group.id === groupId
          ? {
              ...group,
              options: group.options.map((option) =>
                option.id === optionId ? { ...option, name: nameValue } : option,
              ),
            }
          : group,
      ),
    );

  const regenerate = () => {
    const valid = groups
      .filter((group) => group.name.trim() && group.options.some((option) => option.name.trim()))
      .map((group) => ({
        ...group,
        options: group.options.filter((option) => option.name.trim()),
      }));
    setGroups(valid);
    setVariants(buildCombinations(valid, variants, Number(price) || 0));
  };

  const generateArabic = async () => {
    if (!name.trim()) {
      addToast('Enter the English product name first', 'warning');
      return;
    }
    if ((nameAr || descriptionAr) && !window.confirm('Replace the existing Arabic translation?'))
      return;
    setTranslating(true);
    try {
      const response: any = await catalogApi.translateArabic({ name, description });
      setNameAr(response.data.name_ar);
      setDescriptionAr(response.data.description_ar);
      addToast('Arabic translation generated', 'success');
    } catch (error: any) {
      addToast(error.message ?? 'Arabic translation is unavailable', 'danger');
    } finally {
      setTranslating(false);
    }
  };

  const submit = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!name.trim() || !price) {
      addToast('Product name and price are required', 'warning');
      return;
    }
    setSaving(true);
    const product = {
      name: name.trim(),
      nameAr: nameAr.trim(),
      description: description.trim(),
      descriptionAr: descriptionAr.trim(),
      price: Number(price),
      cost: Number(cost) || 0,
      stock: manageStock ? Number(stock) || 0 : 0,
      minStock: Number(minStock) || 0,
      trackStock: manageStock,
      category,
      code: productCode,
      unit: 'pcs',
      imageUrl,
      taxable: true,
      showInOnlineStore: showOnline,
      isFeatured: featured,
      variantGroups: groups,
      variants,
      additionalImagePaths: additionalImagePaths,
      imageFocus: {
        main: mainFocus,
        additional: additionalFocus,
      },
    };
    const filesToUpload = additionalImageFiles.filter((file): file is File => file !== null);
    try {
      if (id) await updateProduct(id, product, imageFile, filesToUpload, removePaths);
      else await createProduct(product, imageFile, filesToUpload);
      addToast(
        editing ? 'Product updated successfully' : 'Product created successfully',
        'success',
      );
      navigate('/products');
    } catch (error: any) {
      addToast(error.message ?? 'Failed to save product', 'danger');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div className="card">Loading product…</div>;

  return (
    <form onSubmit={submit} className="product-editor">
      <header className="page-action-header">
        <div className="inline-actions">
          <button
            type="button"
            className="btn-icon"
            onClick={() => navigate('/products')}
            aria-label="Back to products"
          >
            <AppIcon name="chevronLeft" />
          </button>
          <div>
            <h2>{editing ? 'Edit Product' : 'Add a Product'}</h2>
            <p>Prices are displayed in {currency}</p>
          </div>
        </div>
        <div className="inline-actions">
          <ToggleSwitch
            checked={showOnline}
            onChange={setShowOnline}
            label="Show in Online Store"
          />
          <AppButton type="submit" variant="primary" loading={saving}>
            Save Product
          </AppButton>
        </div>
      </header>

      <div className="product-editor-grid">
        <main className="editor-stack">
          <section className="card editor-section">
            <div className="section-heading">
              <div>
                <h3>Arabic Product Translation</h3>
                <p>Generate editable Arabic product content from the English details.</p>
              </div>
              <AppButton
                type="button"
                variant="secondary"
                onClick={generateArabic}
                loading={translating}
                icon="sparkles"
              >
                Generate Arabic
              </AppButton>
            </div>
            <div className="form-grid-2">
              <TextInput
                label="Arabic Product Name"
                dir="rtl"
                value={nameAr}
                onChange={(event) => setNameAr(event.target.value)}
              />
              <div className="form-group">
                <label className="form-label">Arabic Description</label>
                <textarea
                  dir="rtl"
                  className="form-textarea"
                  rows={3}
                  value={descriptionAr}
                  onChange={(event) => setDescriptionAr(event.target.value)}
                />
              </div>
            </div>
          </section>

          <section className="card editor-section">
            <h3>Basic Product Details</h3>
            <TextInput
              label="Product Name *"
              value={name}
              onChange={(event) => setName(event.target.value)}
            />
            <div className="form-group">
              <label className="form-label">Description</label>
              <textarea
                className="form-textarea"
                rows={4}
                value={description}
                onChange={(event) => setDescription(event.target.value)}
              />
            </div>
            <div className="form-grid-2">
              <TextInput
                label={`Selling Price (${currency}) *`}
                type="number"
                min="0"
                step="0.01"
                value={price}
                onChange={(event) => setPrice(event.target.value)}
              />
              <TextInput
                label={`Cost Price (${currency})`}
                type="number"
                min="0"
                step="0.01"
                value={cost}
                onChange={(event) => setCost(event.target.value)}
              />
              <SelectInput
                label="Category"
                value={category}
                onChange={(event) => setCategory(event.target.value)}
                options={categories.map((item) => ({ value: item.name, label: item.name }))}
              />
              <TextInput
                label="SKU / Barcode"
                value={productCode}
                onChange={(event) => setProductCode(event.target.value)}
              />
            </div>
          </section>

          <section className="card editor-section">
            <div className="section-heading">
              <div>
                <h3>Variation Groups</h3>
                <p>Create groups such as Size and Color, then manage each generated combination.</p>
              </div>
              <AppButton type="button" variant="secondary" onClick={addGroup} icon="plus">
                Add Group
              </AppButton>
            </div>
            {groups.map((group) => (
              <fieldset className="variation-group" key={group.id}>
                <div className="variation-group-head">
                  <TextInput
                    label="Group Name"
                    value={group.name}
                    onChange={(event) => updateGroup(group.id, { name: event.target.value })}
                  />
                  <ToggleSwitch
                    checked={group.required}
                    onChange={(required) => updateGroup(group.id, { required })}
                    label={group.required ? 'Required' : 'Optional'}
                  />
                  <button
                    type="button"
                    className="btn-icon danger"
                    aria-label="Remove group"
                    onClick={() =>
                      setGroups((current) => current.filter((item) => item.id !== group.id))
                    }
                  >
                    <AppIcon name="trash" />
                  </button>
                </div>
                <div className="option-list">
                  {group.options.map((option) => (
                    <div className="option-row" key={option.id}>
                      <TextInput
                        placeholder="Option name, e.g. Large"
                        value={option.name}
                        onChange={(event) => updateOption(group.id, option.id, event.target.value)}
                      />
                      <button
                        type="button"
                        className="btn-icon"
                        aria-label="Remove option"
                        onClick={() =>
                          updateGroup(group.id, {
                            options: group.options.filter((item) => item.id !== option.id),
                          })
                        }
                      >
                        <AppIcon name="close" />
                      </button>
                    </div>
                  ))}
                </div>
                <AppButton
                  type="button"
                  variant="secondary"
                  onClick={() => addOption(group.id)}
                  icon="plus"
                >
                  Add Option
                </AppButton>
              </fieldset>
            ))}
            {groups.length > 0 && (
              <AppButton type="button" variant="primary" onClick={regenerate}>
                Generate Combinations
              </AppButton>
            )}
            {variants.length > 0 && (
              <div className="combination-list">
                {variants.map((variant) => (
                  <div className="combination-row" key={variant.id}>
                    <strong>{variant.name}</strong>
                    <TextInput
                      aria-label={`${variant.name} price`}
                      type="number"
                      step="0.01"
                      value={String(variant.price)}
                      onChange={(event) =>
                        setVariants((current) =>
                          current.map((item) =>
                            item.id === variant.id
                              ? { ...item, price: Number(event.target.value) }
                              : item,
                          ),
                        )
                      }
                    />
                    <TextInput
                      aria-label={`${variant.name} stock`}
                      type="number"
                      value={String(variant.stock)}
                      onChange={(event) =>
                        setVariants((current) =>
                          current.map((item) =>
                            item.id === variant.id
                              ? { ...item, stock: Number(event.target.value) }
                              : item,
                          ),
                        )
                      }
                    />
                    <TextInput
                      aria-label={`${variant.name} SKU`}
                      placeholder="SKU"
                      value={variant.code ?? ''}
                      onChange={(event) =>
                        setVariants((current) =>
                          current.map((item) =>
                            item.id === variant.id ? { ...item, code: event.target.value } : item,
                          ),
                        )
                      }
                    />
                  </div>
                ))}
              </div>
            )}
          </section>
        </main>

        <aside className="editor-stack">
          <section className="card editor-section">
            <h3>Product Photos (Up to 4)</h3>
            <div className="product-image-grid-layout" style={{ display: 'grid', gap: '12px' }}>
              <div className="main-image-slot" style={{ position: 'relative' }}>
                <div
                  className="product-image-drop"
                  onClick={preview ? (e) => handleImageClick(e, 'main') : undefined}
                  style={{ cursor: preview ? 'crosshair' : 'default', position: 'relative' }}
                >
                  {preview ? (
                    <>
                      <img
                        src={preview}
                        alt="Product preview"
                        style={{ objectPosition: mainFocus }}
                      />
                      <button
                        type="button"
                        onClick={(e) => {
                          e.stopPropagation();
                          setImageUrl(undefined);
                          setImageFile(null);
                        }}
                        style={{
                          position: 'absolute',
                          top: '8px',
                          right: '8px',
                          background: 'rgba(0,0,0,0.6)',
                          color: 'white',
                          border: 'none',
                          borderRadius: '50%',
                          width: '28px',
                          height: '28px',
                          display: 'grid',
                          placeItems: 'center',
                          cursor: 'pointer',
                          zIndex: 10,
                        }}
                        aria-label="Remove main image"
                      >
                        <AppIcon name="close" size={14} />
                      </button>
                      <span
                        style={{
                          position: 'absolute',
                          left: mainFocus.split(' ')[0],
                          top: mainFocus.split(' ')[1],
                          transform: 'translate(-50%, -50%)',
                          width: '18px',
                          height: '18px',
                          borderRadius: '50%',
                          border: '2px solid white',
                          background: 'rgba(239, 68, 68, 0.95)',
                          boxShadow: '0 0 6px rgba(0,0,0,0.6)',
                          pointerEvents: 'none',
                          zIndex: 5,
                        }}
                      />
                    </>
                  ) : (
                    <label
                      style={{
                        display: 'grid',
                        placeItems: 'center',
                        width: '100%',
                        height: '100%',
                        cursor: 'pointer',
                      }}
                    >
                      <AppIcon name="products" size={40} />
                      <span style={{ fontSize: '0.8rem', marginTop: '8px' }}>Add Main Photo</span>
                      <input
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        style={{ display: 'none' }}
                        onChange={(event) => setImageFile(event.target.files?.[0] ?? null)}
                      />
                    </label>
                  )}
                </div>
              </div>

              <div
                className="additional-images-row"
                style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '8px' }}
              >
                {[0, 1, 2].map((i) => {
                  const uCount = additionalImages.length;
                  const isUploaded = i < uCount;
                  const fileIdx = i - uCount;

                  let slotPreview: string | null = null;
                  let slotType: 'uploaded' | 'file' = 'file';

                  if (isUploaded) {
                    slotPreview = additionalImages[i];
                    slotType = 'uploaded';
                  } else if (additionalImageFiles[fileIdx]) {
                    slotPreview = URL.createObjectURL(additionalImageFiles[fileIdx]!);
                    slotType = 'file';
                  }

                  const [addX, addY] = (additionalFocus[i] || '50% 50%').split(' ');

                  return (
                    <div
                      key={i}
                      className="additional-image-slot"
                      onClick={
                        slotPreview ? (e) => handleImageClick(e, 'additional', i) : undefined
                      }
                      style={{
                        aspectRatio: '1',
                        border: '1px dashed var(--color-border)',
                        borderRadius: 'var(--radius-md)',
                        display: 'grid',
                        placeItems: 'center',
                        overflow: 'hidden',
                        position: 'relative',
                        background: 'var(--color-bg)',
                        cursor: slotPreview ? 'crosshair' : 'default',
                      }}
                    >
                      {slotPreview ? (
                        <>
                          <img
                            src={slotPreview}
                            alt={`Additional ${i}`}
                            style={{
                              width: '100%',
                              height: '100%',
                              objectFit: 'cover',
                              objectPosition: additionalFocus[i] || '50% 50%',
                            }}
                          />
                          <button
                            type="button"
                            onClick={(e) => {
                              e.stopPropagation();
                              handleRemoveSlot(i, slotType);
                            }}
                            style={{
                              position: 'absolute',
                              top: '4px',
                              right: '4px',
                              background: 'rgba(0,0,0,0.6)',
                              color: 'white',
                              border: 'none',
                              borderRadius: '50%',
                              width: '20px',
                              height: '20px',
                              display: 'grid',
                              placeItems: 'center',
                              cursor: 'pointer',
                              zIndex: 10,
                            }}
                            aria-label={`Remove photo ${i + 2}`}
                          >
                            <AppIcon name="close" size={10} />
                          </button>
                          <span
                            style={{
                              position: 'absolute',
                              left: addX,
                              top: addY,
                              transform: 'translate(-50%, -50%)',
                              width: '14px',
                              height: '14px',
                              borderRadius: '50%',
                              border: '1.5px solid white',
                              background: 'rgba(239, 68, 68, 0.95)',
                              boxShadow: '0 0 4px rgba(0,0,0,0.6)',
                              pointerEvents: 'none',
                              zIndex: 5,
                            }}
                          />
                        </>
                      ) : (
                        <label
                          style={{
                            display: 'grid',
                            placeItems: 'center',
                            width: '100%',
                            height: '100%',
                            cursor: 'pointer',
                          }}
                        >
                          <AppIcon name="plus" size={18} />
                          <span
                            style={{
                              fontSize: '0.65rem',
                              marginTop: '4px',
                              color: 'var(--text-muted)',
                            }}
                          >
                            Add Photo
                          </span>
                          <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            style={{ display: 'none' }}
                            onChange={(event) => {
                              const file = event.target.files?.[0];
                              if (file) handleFileChange(file, fileIdx);
                            }}
                          />
                        </label>
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          </section>
          <section className="card editor-section">
            <h3>Inventory</h3>
            <ToggleSwitch checked={manageStock} onChange={setManageStock} label="Track Stock" />
            {manageStock && (
              <div className="form-grid-2">
                <TextInput
                  label="On Hand"
                  type="number"
                  value={stock}
                  onChange={(event) => setStock(event.target.value)}
                />
                <TextInput
                  label="Low-stock Alert"
                  type="number"
                  value={minStock}
                  onChange={(event) => setMinStock(event.target.value)}
                />
              </div>
            )}
            <ToggleSwitch checked={featured} onChange={setFeatured} label="Featured Product" />
          </section>
        </aside>
      </div>
    </form>
  );
};
