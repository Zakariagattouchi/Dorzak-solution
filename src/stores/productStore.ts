import { create } from 'zustand';
import { Product, Category } from '../data/mockData';
import { catalogApi } from '../api/endpoints';
import { toProduct, toCategory, productPayload } from '../api/adapters';
import {
  captureMerchantScope,
  isMerchantScopeCurrent,
  requireCurrentMerchantScope,
} from './merchantScope';

interface ProductState {
  products: Product[];
  categories: Category[];
  loading: boolean;
  error: string | null;
  fetchProducts: () => Promise<void>;
  fetchCategories: () => Promise<void>;
  addProduct: (product: Omit<Product, 'id'>) => Promise<Product>;
  getProduct: (id: string) => Promise<Product>;
  updateProduct: (
    id: string,
    product: Partial<Product>,
    imageFile?: File | null,
    additionalImageFiles?: File[],
    removePaths?: string[],
  ) => Promise<Product>;
  createProduct: (
    product: Omit<Product, 'id'>,
    imageFile?: File | null,
    additionalImageFiles?: File[],
  ) => Promise<Product>;
  addCategory: (
    name: string,
    color?: string,
    imageFile?: File | null,
    description?: string,
  ) => Promise<Category>;
  updateCategory: (
    id: string,
    name: string,
    color?: string,
    imageFile?: File | null,
    description?: string,
  ) => Promise<Category>;
  deleteProduct: (id: string) => Promise<void>;
}

export const useProductStore = create<ProductState>((set, get) => ({
  products: [],
  categories: [],
  loading: false,
  error: null,

  fetchProducts: async () => {
    const scope = captureMerchantScope();
    set({ loading: true, error: null });
    try {
      const res: any = await catalogApi.products({ per_page: 200 });
      requireCurrentMerchantScope(scope);
      set({ products: res.data.map(toProduct), loading: false });
    } catch (e: any) {
      if (!isMerchantScopeCurrent(scope)) return;
      set({ loading: false, error: e.message ?? 'Failed to load products' });
    }
  },

  fetchCategories: async () => {
    const scope = captureMerchantScope();
    try {
      const res: any = await catalogApi.categories();
      requireCurrentMerchantScope(scope);
      set({ categories: res.data.map(toCategory) });
    } catch (e: any) {
      if (!isMerchantScopeCurrent(scope)) return;
      set({ error: e.message ?? 'Failed to load categories' });
    }
  },

  addProduct: async (product) => {
    const scope = captureMerchantScope();
    const res: any = await catalogApi.createProduct(productPayload(product, get().categories));
    requireCurrentMerchantScope(scope);
    const created = toProduct(res.data);
    set((s) => ({ products: [created, ...s.products] }));
    return created;
  },

  getProduct: async (id) => {
    const scope = captureMerchantScope();
    const cached = get().products.find((product) => product.id === id);
    if (cached) return cached;
    const res: any = await catalogApi.product(Number(id));
    requireCurrentMerchantScope(scope);
    return toProduct(res.data);
  },

  createProduct: async (product, imageFile, additionalImageFiles = []) => {
    const scope = captureMerchantScope();
    const res: any = await catalogApi.createProduct(productPayload(product, get().categories));
    requireCurrentMerchantScope(scope);
    let created = toProduct(res.data);
    if (imageFile) {
      await catalogApi.uploadProductImage(Number(created.id), imageFile);
      requireCurrentMerchantScope(scope);
    }
    if (additionalImageFiles.length > 0) {
      for (const file of additionalImageFiles) {
        requireCurrentMerchantScope(scope);
        await catalogApi.uploadProductAdditionalImage(Number(created.id), file);
        requireCurrentMerchantScope(scope);
      }
    }
    requireCurrentMerchantScope(scope);
    const fresh: any = await catalogApi.product(Number(created.id));
    requireCurrentMerchantScope(scope);
    created = toProduct(fresh.data);
    set((s) => ({ products: [created, ...s.products.filter((p) => p.id !== created.id)] }));
    return created;
  },

  updateProduct: async (id, product, imageFile, additionalImageFiles = [], removePaths = []) => {
    const scope = captureMerchantScope();
    if (removePaths.length > 0) {
      for (const path of removePaths) {
        requireCurrentMerchantScope(scope);
        await catalogApi.deleteProductAdditionalImage(Number(id), path);
        requireCurrentMerchantScope(scope);
      }
    }
    requireCurrentMerchantScope(scope);
    const res: any = await catalogApi.updateProduct(
      Number(id),
      productPayload(product, get().categories),
    );
    requireCurrentMerchantScope(scope);
    let updated = toProduct(res.data);
    if (imageFile) {
      await catalogApi.uploadProductImage(Number(id), imageFile);
      requireCurrentMerchantScope(scope);
    }
    if (additionalImageFiles.length > 0) {
      for (const file of additionalImageFiles) {
        requireCurrentMerchantScope(scope);
        await catalogApi.uploadProductAdditionalImage(Number(id), file);
        requireCurrentMerchantScope(scope);
      }
    }
    requireCurrentMerchantScope(scope);
    const fresh: any = await catalogApi.product(Number(id));
    requireCurrentMerchantScope(scope);
    updated = toProduct(fresh.data);
    set((s) => ({ products: s.products.map((p) => (p.id === id ? updated : p)) }));
    return updated;
  },

  addCategory: async (name, color, imageFile, description) => {
    const scope = captureMerchantScope();
    const res: any = await catalogApi.createCategory({ name, color, description });
    requireCurrentMerchantScope(scope);
    let created = toCategory(res.data);
    if (imageFile) {
      await catalogApi.uploadCategoryImage(Number(created.id), imageFile);
      requireCurrentMerchantScope(scope);
      const all: any = await catalogApi.categories();
      requireCurrentMerchantScope(scope);
      created = toCategory(all.data.find((item: any) => String(item.id) === created.id));
    }
    set((s) => ({ categories: [...s.categories, created] }));
    return created;
  },

  updateCategory: async (id, name, color, imageFile, description) => {
    const scope = captureMerchantScope();
    const res: any = await catalogApi.updateCategory(Number(id), { name, color, description });
    requireCurrentMerchantScope(scope);
    let updated = toCategory(res.data);
    if (imageFile) {
      await catalogApi.uploadCategoryImage(Number(id), imageFile);
      requireCurrentMerchantScope(scope);
      const all: any = await catalogApi.categories();
      requireCurrentMerchantScope(scope);
      updated = toCategory(all.data.find((item: any) => String(item.id) === id));
    }
    set((s) => ({
      categories: s.categories.map((category) =>
        category.id === id ? { ...updated, productCount: category.productCount } : category,
      ),
    }));
    return updated;
  },

  deleteProduct: async (id) => {
    const scope = captureMerchantScope();
    await catalogApi.deleteProduct(Number(id));
    requireCurrentMerchantScope(scope);
    set((s) => ({ products: s.products.filter((p) => p.id !== id) }));
  },
}));
