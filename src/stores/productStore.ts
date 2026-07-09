import { create } from 'zustand';
import { Product, Category } from '../data/mockData';
import { catalogApi } from '../api/endpoints';
import { toProduct, toCategory, productPayload } from '../api/adapters';

interface ProductState {
  products: Product[];
  categories: Category[];
  loading: boolean;
  error: string | null;
  fetchProducts: () => Promise<void>;
  fetchCategories: () => Promise<void>;
  addProduct: (product: Omit<Product, 'id'>) => Promise<Product>;
  getProduct: (id: string) => Promise<Product>;
  updateProduct: (id: string, product: Partial<Product>, imageFile?: File | null, additionalImageFiles?: File[], removePaths?: string[]) => Promise<Product>;
  createProduct: (product: Omit<Product, 'id'>, imageFile?: File | null, additionalImageFiles?: File[]) => Promise<Product>;
  addCategory: (name: string, color?: string, imageFile?: File | null, description?: string) => Promise<Category>;
  updateCategory: (id: string, name: string, color?: string, imageFile?: File | null, description?: string) => Promise<Category>;
  deleteProduct: (id: string) => Promise<void>;
}

export const useProductStore = create<ProductState>((set, get) => ({
  products: [],
  categories: [],
  loading: false,
  error: null,

  fetchProducts: async () => {
    set({ loading: true, error: null });
    try {
      const res: any = await catalogApi.products({ per_page: 200 });
      set({ products: res.data.map(toProduct), loading: false });
    } catch (e: any) {
      set({ loading: false, error: e.message ?? 'Failed to load products' });
    }
  },

  fetchCategories: async () => {
    try {
      const res: any = await catalogApi.categories();
      set({ categories: res.data.map(toCategory) });
    } catch (e: any) {
      set({ error: e.message ?? 'Failed to load categories' });
    }
  },

  addProduct: async (product) => {
    const res: any = await catalogApi.createProduct(productPayload(product, get().categories));
    const created = toProduct(res.data);
    set((s) => ({ products: [created, ...s.products] }));
    return created;
  },

  getProduct: async (id) => {
    const cached = get().products.find((product) => product.id === id);
    if (cached) return cached;
    const res: any = await catalogApi.product(Number(id));
    return toProduct(res.data);
  },

  createProduct: async (product, imageFile, additionalImageFiles = []) => {
    const res: any = await catalogApi.createProduct(productPayload(product, get().categories));
    let created = toProduct(res.data);
    if (imageFile) {
      await catalogApi.uploadProductImage(Number(created.id), imageFile);
    }
    if (additionalImageFiles.length > 0) {
      for (const file of additionalImageFiles) {
        await catalogApi.uploadProductAdditionalImage(Number(created.id), file);
      }
    }
    created = await get().getProduct(created.id);
    set((s) => ({ products: [created, ...s.products.filter((p) => p.id !== created.id)] }));
    return created;
  },

  updateProduct: async (id, product, imageFile, additionalImageFiles = [], removePaths = []) => {
    if (removePaths.length > 0) {
      for (const path of removePaths) {
        await catalogApi.deleteProductAdditionalImage(Number(id), path);
      }
    }
    const res: any = await catalogApi.updateProduct(Number(id), productPayload(product, get().categories));
    let updated = toProduct(res.data);
    if (imageFile) {
      await catalogApi.uploadProductImage(Number(id), imageFile);
    }
    if (additionalImageFiles.length > 0) {
      for (const file of additionalImageFiles) {
        await catalogApi.uploadProductAdditionalImage(Number(id), file);
      }
    }
    const fresh: any = await catalogApi.product(Number(id));
    updated = toProduct(fresh.data);
    set((s) => ({ products: s.products.map((p) => (p.id === id ? updated : p)) }));
    return updated;
  },

  addCategory: async (name, color, imageFile, description) => {
    const res: any = await catalogApi.createCategory({ name, color, description });
    let created = toCategory(res.data);
    if (imageFile) {
      await catalogApi.uploadCategoryImage(Number(created.id), imageFile);
      const all: any = await catalogApi.categories();
      created = toCategory(all.data.find((item: any) => String(item.id) === created.id));
    }
    set((s) => ({ categories: [...s.categories, created] }));
    return created;
  },

  updateCategory: async (id, name, color, imageFile, description) => {
    const res: any = await catalogApi.updateCategory(Number(id), { name, color, description });
    let updated = toCategory(res.data);
    if (imageFile) {
      await catalogApi.uploadCategoryImage(Number(id), imageFile);
      const all: any = await catalogApi.categories();
      updated = toCategory(all.data.find((item: any) => String(item.id) === id));
    }
    set((s) => ({ categories: s.categories.map((category) => category.id === id ? { ...updated, productCount: category.productCount } : category) }));
    return updated;
  },

  deleteProduct: async (id) => {
    await catalogApi.deleteProduct(Number(id));
    set((s) => ({ products: s.products.filter((p) => p.id !== id) }));
  },
}));
