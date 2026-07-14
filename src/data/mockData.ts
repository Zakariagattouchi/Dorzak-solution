export interface ProductVariant {
  id: string;
  name: string; // e.g. "Size: M, Color: Blue"
  optionValues: Record<string, string>;
  price: number;
  stock: number;
  code?: string;
  isActive: boolean;
}

export interface ProductVariationOption {
  id: string;
  name: string;
  nameAr?: string;
}

export interface ProductVariationGroup {
  id: string;
  name: string;
  nameAr?: string;
  required: boolean;
  options: ProductVariationOption[];
}

export interface Product {
  id: string;
  name: string;
  nameAr?: string;
  description?: string;
  descriptionAr?: string;
  price: number;
  cost: number;
  stock: number;
  minStock: number;
  trackStock: boolean;
  category: string;
  code: string;
  unit: string; // e.g. "pcs", "kg", "box", "m"
  imageUrl?: string;
  additionalImages?: string[];
  additionalImagePaths?: string[];
  imageFocus?: {
    main?: string;
    additional?: string[];
  };
  taxable: boolean;
  showInOnlineStore: boolean;
  isFeatured: boolean;
  variants: ProductVariant[];
  variantGroups: ProductVariationGroup[];
}

export interface Category {
  id: string;
  name: string;
  productCount: number;
  color?: string;
  imageUrl?: string;
  imagePath?: string;
  description?: string;
}

export interface Customer {
  id: string;
  name: string;
  email: string;
  phone: string;
  address?: string;
  addressDetails?: string;
  city?: string;
  latitude?: number;
  longitude?: number;
  taxId?: string;
  totalOrders: number;
  totalSpent: number;
  notes?: string;
}

export interface OrderItem {
  productId: string;
  productName: string;
  quantity: number;
  unitPrice: number;
  variantName?: string;
  variantId?: string;
}

export type OrderStatus =
  | 'CONFIRMING'
  | 'ACCEPTED'
  | 'PREPARING'
  | 'OUT_FOR_DELIVERY'
  | 'COMPLETE'
  | 'CANCELLED';
export type PaymentStatus = 'UNPAID' | 'PENDING_VERIFICATION' | 'PAID' | 'FAILED' | 'REFUNDED';

export interface Order {
  apiId?: string;
  id: string;
  customerName: string;
  customerPhone?: string;
  date: string;
  items: OrderItem[];
  subtotal: number;
  discount: number;
  taxAmount: number;
  total: number;
  status: OrderStatus;
  paymentStatus: PaymentStatus;
  paymentMethod: 'CASH' | 'CARD' | 'TRANSFER' | 'WHATSAPP' | 'FAWRAN';
  currencyCode: string;
  fulfillment?: 'DELIVERY' | 'PICKUP' | 'DINE_IN';
  tableNumber?: number;
  deliveryAddress?: string;
  deliveryCity?: string;
  deliveryLatitude?: number;
  deliveryLongitude?: number;
  deliveryFee?: number;
  deliveryFeeStatus?: 'QUOTED' | 'PENDING' | 'SET';
  deliveryProviderName?: string;
  deliveryDistanceKm?: number;
  pickupLatitude?: number;
  pickupLongitude?: number;
  pickupAddress?: string;
  paymentReference?: string;
  hasPaymentProof?: boolean;
  notes?: string;
  // Network-delivery courier state (only present for Dorzak-dispatched orders)
  courierState?: string;
  deliveryDispatchedAt?: string;
  deliveryExternalReference?: string;
}

/**
 * Empty shell for a signed-in merchant's settings before the API responds.
 * These are placeholders ONLY — never a real store's identity. Seeding this
 * with a real owner/email/address leaked one merchant's details into every
 * other merchant's Settings page (and could be saved over their own data).
 */
export const initialAccountInfo = {
  id: '',
  businessName: '',
  tagline: '',
  ownerName: '',
  email: '',
  phone: '',
  whatsapp: '',
  address: '',
  city: '',
  state: '',
  zipCode: '',
  country: 'United States',
  latitude: null as number | null,
  longitude: null as number | null,
  language: 'en' as 'en' | 'ar',
  currency: 'USD',
  currencySymbol: '$',
  symbolPlacement: 'BEFORE' as 'BEFORE' | 'AFTER',
  taxRate: 0,
  taxId: '',
  taxIncludedInPrice: false,
  chargeSalesTax: true,

  // Online Storefront
  onlineStoreEnabled: false,
  storeSlug: '',
  publicUrl: '',
  bannerUrl: '',
  logoUrl: '',
  storeAccentColor: '#1890ff',
  storeSecondaryColor: '#373f4e',
  storeBio: '',
  allowDelivery: true,
  allowPickup: true,
  allowDineIn: false,
  dineInTableCount: 0,
  deliveryFee: 0,
  freeDeliveryThreshold: 0,
  minOrderAmount: 0,
  whatsappOrderingEnabled: true,
  whatsappDeliveryFallback: false,
  showOutOfStockOnline: true,
  fawranEnabled: false,
  fawranAlias: '',
  fawranMobile: '',
  fawranIban: '',
  productCardLayout: 'vertical' as 'vertical' | 'horizontal',
  showStoreHeader: true,
  showStoreGradient: true,
  storeNavbarColor: '#17201e',

  // Receipts
  receiptHeader: '',
  receiptFooter: '',
  receiptShowLogo: true,
  receiptShowAddress: true,
  receiptShowTax: true,
  autoPrintReceipt: false,
};

export const initialCategories: Category[] = [
  {
    id: 'cat_1',
    name: 'Apparel & Fashion',
    productCount: 14,
    color: '#3b82f6',
    imageUrl: 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600',
  },
  {
    id: 'cat_2',
    name: 'Electronics & Tech',
    productCount: 8,
    color: '#10b981',
    imageUrl: 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600',
  },
  {
    id: 'cat_3',
    name: 'Coffee & Beverages',
    productCount: 12,
    color: '#f59e0b',
    imageUrl: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600',
  },
  {
    id: 'cat_4',
    name: 'Accessories',
    productCount: 6,
    color: '#ec4899',
    imageUrl: 'https://images.unsplash.com/photo-1523779917675-b6ed3a42a561?w=600',
  },
  {
    id: 'cat_5',
    name: 'Home & Office',
    productCount: 8,
    color: '#8b5cf6',
    imageUrl: 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=600',
  },
];

export const initialProducts: Product[] = [
  {
    id: 'prod_101',
    name: 'Dorzak Signature Cotton Hoodie',
    description:
      'Ultra-soft heavyweight fleece hoodie made from 100% organic combed cotton with double-stitched hood and ribbed cuffs.',
    price: 49.99,
    cost: 18.0,
    stock: 45,
    minStock: 10,
    trackStock: true,
    category: 'Apparel & Fashion',
    code: 'HOOD-001',
    unit: 'pcs',
    imageUrl: 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=500',
    taxable: true,
    showInOnlineStore: true,
    isFeatured: true,
    variants: [
      {
        id: 'v_1',
        name: 'Small / Black',
        optionValues: { size: 'small', color: 'black' },
        price: 49.99,
        stock: 15,
        code: 'HOOD-S-BLK',
        isActive: true,
      },
      {
        id: 'v_2',
        name: 'Medium / Black',
        optionValues: { size: 'medium', color: 'black' },
        price: 49.99,
        stock: 20,
        code: 'HOOD-M-BLK',
        isActive: true,
      },
      {
        id: 'v_3',
        name: 'Large / Black',
        optionValues: { size: 'large', color: 'black' },
        price: 49.99,
        stock: 10,
        code: 'HOOD-L-BLK',
        isActive: true,
      },
    ],
    variantGroups: [
      {
        id: 'size',
        name: 'Size',
        required: true,
        options: [
          { id: 'small', name: 'Small' },
          { id: 'medium', name: 'Medium' },
          { id: 'large', name: 'Large' },
        ],
      },
      { id: 'color', name: 'Color', required: true, options: [{ id: 'black', name: 'Black' }] },
    ],
  },
  {
    id: 'prod_102',
    name: 'Wireless Noise-Canceling Earbuds',
    description:
      'Active noise cancellation with 32-hour total battery life, IPX5 water resistance, and crystal-clear microphone.',
    price: 89.95,
    cost: 35.0,
    stock: 22,
    minStock: 5,
    trackStock: true,
    category: 'Electronics & Tech',
    code: 'EAR-900',
    unit: 'pcs',
    imageUrl: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500',
    taxable: true,
    showInOnlineStore: true,
    isFeatured: true,
    variants: [],
    variantGroups: [],
  },
  {
    id: 'prod_103',
    name: 'Artisan Cold Brew Coffee (750ml)',
    description:
      'Slow-steeped for 24 hours using single-origin Colombian beans. Smooth, rich, with dark chocolate notes.',
    price: 8.5,
    cost: 2.2,
    stock: 120,
    minStock: 20,
    trackStock: true,
    category: 'Coffee & Beverages',
    code: 'COFF-01',
    unit: 'bottle',
    imageUrl: 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=500',
    taxable: false,
    showInOnlineStore: true,
    isFeatured: false,
    variants: [],
    variantGroups: [],
  },
  {
    id: 'prod_104',
    name: 'Minimalist Leather Cardholder',
    description:
      'Handcrafted full-grain Italian leather slim wallet holding up to 8 cards plus cash.',
    price: 29.0,
    cost: 8.5,
    stock: 35,
    minStock: 8,
    trackStock: true,
    category: 'Accessories',
    code: 'LTHR-CARD',
    unit: 'pcs',
    imageUrl: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500',
    taxable: true,
    showInOnlineStore: true,
    isFeatured: true,
    variants: [],
    variantGroups: [],
  },
  {
    id: 'prod_105',
    name: 'Ergonomic Desk Mat',
    description:
      'Waterproof felt & vegan leather dual-sided desk pad for smooth mouse tracking and desk protection.',
    price: 34.5,
    cost: 11.0,
    stock: 18,
    minStock: 5,
    trackStock: true,
    category: 'Home & Office',
    code: 'MAT-002',
    unit: 'pcs',
    imageUrl: 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500',
    taxable: true,
    showInOnlineStore: true,
    isFeatured: false,
    variants: [],
    variantGroups: [],
  },
  {
    id: 'prod_106',
    name: 'Stainless Steel Water Bottle (1L)',
    description:
      'Double-wall vacuum insulated flask keeping drinks cold for 24 hours or hot for 12 hours.',
    price: 24.99,
    cost: 6.0,
    stock: 60,
    minStock: 15,
    trackStock: true,
    category: 'Accessories',
    code: 'BTL-1000',
    unit: 'pcs',
    imageUrl: 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500',
    taxable: true,
    showInOnlineStore: true,
    isFeatured: false,
    variants: [],
    variantGroups: [],
  },
];

export const initialCustomers: Customer[] = [
  {
    id: 'cust_1',
    name: 'Sarah Jenkins',
    email: 'sarah.j@example.com',
    phone: '+1 555-0144',
    address: '123 Market St',
    city: 'San Francisco',
    totalOrders: 12,
    totalSpent: 480.5,
  },
  {
    id: 'cust_2',
    name: 'David Miller',
    email: 'dmiller@techcorp.io',
    phone: '+1 555-0812',
    address: '456 Tech Way',
    city: 'San Jose',
    totalOrders: 5,
    totalSpent: 210.0,
  },
  {
    id: 'cust_3',
    name: 'Elena Rostova',
    email: 'elena@designstudio.com',
    phone: '+1 555-0923',
    address: '789 Design Blvd',
    city: 'Oakland',
    totalOrders: 8,
    totalSpent: 345.9,
  },
  {
    id: 'cust_4',
    name: 'Michael Vance',
    email: 'mvance@startup.co',
    phone: '+1 555-0377',
    address: '101 Startup Alley',
    city: 'San Francisco',
    totalOrders: 3,
    totalSpent: 115.0,
  },
];

export const initialOrders: Order[] = [
  {
    id: 'ORD-9821',
    customerName: 'Sarah Jenkins',
    customerPhone: '+1 555-0144',
    date: '2026-07-05 14:32',
    items: [
      {
        productId: 'prod_101',
        productName: 'Dorzak Signature Cotton Hoodie',
        quantity: 2,
        unitPrice: 49.99,
        variantName: 'Medium / Black',
      },
      {
        productId: 'prod_103',
        productName: 'Artisan Cold Brew Coffee',
        quantity: 1,
        unitPrice: 8.5,
      },
    ],
    subtotal: 108.48,
    discount: 5.0,
    taxAmount: 8.79,
    total: 112.27,
    status: 'COMPLETE',
    paymentStatus: 'PAID',
    paymentMethod: 'CARD',
    currencyCode: 'USD',
    notes: 'Customer requested gift receipt',
  },
  {
    id: 'ORD-9820',
    customerName: 'David Miller',
    customerPhone: '+1 555-0812',
    date: '2026-07-05 11:15',
    items: [
      {
        productId: 'prod_102',
        productName: 'Wireless Noise-Canceling Earbuds',
        quantity: 1,
        unitPrice: 89.95,
      },
    ],
    subtotal: 89.95,
    discount: 0,
    taxAmount: 7.65,
    total: 97.6,
    status: 'COMPLETE',
    paymentStatus: 'PAID',
    paymentMethod: 'CASH',
    currencyCode: 'USD',
  },
  {
    id: 'ORD-9819',
    customerName: 'Elena Rostova',
    customerPhone: '+1 555-0923',
    date: '2026-07-04 16:45',
    items: [
      {
        productId: 'prod_104',
        productName: 'Minimalist Leather Cardholder',
        quantity: 1,
        unitPrice: 29.0,
      },
      { productId: 'prod_105', productName: 'Ergonomic Desk Mat', quantity: 1, unitPrice: 34.5 },
    ],
    subtotal: 63.5,
    discount: 0,
    taxAmount: 5.4,
    total: 68.9,
    status: 'CONFIRMING',
    paymentStatus: 'UNPAID',
    paymentMethod: 'TRANSFER',
    currencyCode: 'USD',
  },
];
