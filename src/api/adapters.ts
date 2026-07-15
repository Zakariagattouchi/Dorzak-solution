// Maps between the Laravel API DTO shapes and the frontend's existing interfaces
// (data/mockData.ts). Keeping the frontend shapes lets the components stay unchanged
// while the stores talk to the real backend. See docs/backend-planning/09-*.

import {
  Product,
  ProductVariant,
  Category,
  Customer,
  Order,
  OrderItem,
  initialAccountInfo,
} from '../data/mockData';

type Account = typeof initialAccountInfo;

const num = (v: unknown): number => (v === null || v === undefined ? 0 : Number(v));

/* ---------- API -> frontend ---------- */

export function toProduct(api: any): Product {
  return {
    id: String(api.id),
    name: api.name,
    nameAr: api.name_ar ?? '',
    description: api.description ?? '',
    descriptionAr: api.description_ar ?? '',
    price: num(api.price),
    cost: num(api.cost),
    stock: num(api.stock),
    minStock: num(api.min_stock),
    trackStock: !!api.track_stock,
    category: api.category?.name ?? '',
    code: api.sku ?? '',
    unit: api.unit ?? 'pcs',
    imageUrl: api.image_url ?? undefined,
    additionalImages: api.additional_image_urls ?? api.additional_images ?? [],
    additionalImagePaths: api.additional_images ?? [],
    imageFocus: api.image_focus ?? undefined,
    taxable: !!api.taxable,
    showInOnlineStore: !!api.show_in_online_store,
    isFeatured: !!api.is_featured,
    variants: (api.variants ?? []).map(toVariant),
    variantGroups: api.variant_groups ?? [],
  };
}

function toVariant(api: any): ProductVariant {
  return {
    id: String(api.id),
    name: api.name,
    optionValues: api.option_values ?? {},
    price: num(api.price),
    stock: api.stock !== undefined ? num(api.stock) : api.in_stock ? 9999 : 0,
    code: api.sku ?? undefined,
    isActive: api.is_active ?? true,
  };
}

export function toCategory(api: any): Category {
  return {
    id: String(api.id),
    name: api.name,
    productCount: num(api.products_count),
    color: api.color,
    imageUrl: api.image_url ?? undefined,
    imagePath: api.image_path ?? undefined,
    description: api.description ?? '',
  };
}

export function toCustomer(api: any): Customer {
  return {
    id: String(api.id),
    name: api.name,
    email: api.email ?? '',
    phone: api.phone ?? '',
    address: api.address ?? undefined,
    addressDetails: api.address_details ?? undefined,
    city: api.city ?? undefined,
    latitude: api.latitude == null ? undefined : num(api.latitude),
    longitude: api.longitude == null ? undefined : num(api.longitude),
    taxId: api.tax_id ?? undefined,
    totalOrders: num(api.total_orders),
    totalSpent: num(api.total_spent),
    notes: api.notes ?? undefined,
  };
}

export function toOrder(api: any): Order {
  return {
    apiId: String(api.id),
    id: api.order_number,
    customerName: api.customer_name,
    customerPhone: api.customer_phone ?? undefined,
    date: api.placed_at_local ?? (api.placed_at ?? '').slice(0, 16).replace('T', ' '),
    items: (api.items ?? []).map(toOrderItem),
    subtotal: num(api.subtotal),
    discount: num(api.discount),
    taxAmount: num(api.tax_amount),
    total: num(api.total),
    status: api.status,
    paymentStatus: api.payment_status ?? (api.status === 'COMPLETE' ? 'PAID' : 'UNPAID'),
    paymentMethod: api.payment_method,
    currencyCode: api.currency_code ?? 'QAR',
    fulfillment: api.fulfillment ?? undefined,
    tableNumber: api.table_number == null ? undefined : num(api.table_number),
    deliveryAddress: api.delivery_address ?? undefined,
    deliveryCity: api.delivery_city ?? undefined,
    deliveryLatitude: api.delivery_latitude != null ? Number(api.delivery_latitude) : undefined,
    deliveryLongitude: api.delivery_longitude != null ? Number(api.delivery_longitude) : undefined,
    deliveryFee: num(api.delivery_fee),
    deliveryFeeStatus: api.delivery_fee_status ?? undefined,
    deliveryProviderName: api.delivery_provider_name ?? undefined,
    deliveryDistanceKm:
      api.delivery_distance_km != null ? Number(api.delivery_distance_km) : undefined,
    pickupLatitude: api.pickup_latitude != null ? Number(api.pickup_latitude) : undefined,
    pickupLongitude: api.pickup_longitude != null ? Number(api.pickup_longitude) : undefined,
    pickupAddress: api.pickup_address ?? undefined,
    paymentReference: api.payment_reference ?? undefined,
    hasPaymentProof: !!api.has_payment_proof,
    notes: api.notes ?? undefined,
    courierState: api.courier_state ?? undefined,
    deliveryDispatchedAt: api.delivery_dispatched_at ?? undefined,
    deliveryExternalReference: api.delivery_external_reference ?? undefined,
  };
}

function toOrderItem(api: any): OrderItem {
  return {
    productId: String(api.product_id ?? ''),
    productName: api.product_name,
    quantity: num(api.quantity),
    unitPrice: num(api.unit_price),
    variantName: api.variant_name ?? undefined,
    variantId: api.variant_id == null ? undefined : String(api.variant_id),
  };
}

/* ---------- settings envelope <-> flat accountInfo ---------- */

export function toAccountInfo(env: any, storeId?: string): Partial<Account> {
  const g = env.general ?? {},
    b = env.business ?? {},
    c = env.currency ?? {},
    t = env.taxes ?? {},
    r = env.receipts ?? {},
    s = env.storefront ?? {};
  return {
    ...(storeId ? { id: storeId } : {}),
    businessName: g.business_name,
    tagline: g.tagline ?? '',
    phone: g.phone ?? '',
    whatsapp: g.whatsapp ?? '',
    language: g.language,
    ownerName: b.owner_name ?? '',
    email: b.email ?? '',
    address: b.address ?? '',
    city: b.city ?? '',
    state: b.state ?? '',
    zipCode: b.zip_code ?? '',
    country: b.country,
    latitude: b.latitude ?? null,
    longitude: b.longitude ?? null,
    currency: c.currency,
    currencySymbol: c.currency_symbol,
    symbolPlacement: c.symbol_placement,
    taxRate: num(t.tax_rate),
    taxId: t.tax_id ?? '',
    taxIncludedInPrice: !!t.tax_included_in_price,
    chargeSalesTax: t.charge_sales_tax ?? true,
    receiptHeader: r.header ?? '',
    receiptFooter: r.footer ?? '',
    receiptShowLogo: !!r.show_logo,
    receiptShowAddress: !!r.show_address,
    receiptShowTax: !!r.show_tax,
    autoPrintReceipt: !!r.auto_print,
    onlineStoreEnabled: !!s.online_store_enabled,
    storeSlug: s.slug ?? '',
    publicUrl: s.public_url ?? '',
    storeBio: s.bio ?? '',
    bannerUrl: s.banner_url ?? '',
    logoUrl: s.logo_url ?? '',
    storeAccentColor: s.accent_color ?? '#1890ff',
    storeSecondaryColor: s.secondary_color ?? '#373f4e',
    allowDelivery: !!s.allow_delivery,
    allowPickup: !!s.allow_pickup,
    allowDineIn: !!s.allow_dine_in,
    dineInTableCount: num(s.dine_in_table_count),
    deliveryFee: num(s.delivery_fee),
    freeDeliveryThreshold: num(s.free_delivery_threshold),
    minOrderAmount: num(s.min_order_amount),
    whatsappOrderingEnabled: !!s.whatsapp_ordering_enabled,
    whatsappDeliveryFallback: !!s.whatsapp_delivery_fallback,
    showOutOfStockOnline: !!s.show_out_of_stock_online,
    fawranEnabled: !!s.fawran_enabled,
    fawranAlias: s.fawran_alias ?? '',
    fawranMobile: s.fawran_mobile ?? '',
    fawranIban: s.fawran_iban ?? '',
    productCardLayout: (s.product_card_layout as 'vertical' | 'horizontal') || 'vertical',
    showStoreHeader: s.show_store_header ?? true,
    showStoreGradient: s.show_store_gradient ?? true,
    storeNavbarColor: s.navbar_color ?? '#17201e',
  } as Partial<Account>;
}

/** Which flat accountInfo keys belong to each settings group. */
const GROUP_KEYS: Record<string, (keyof Account)[]> = {
  general: ['businessName', 'tagline', 'phone', 'whatsapp', 'language'],
  business: [
    'ownerName',
    'email',
    'address',
    'city',
    'state',
    'zipCode',
    'country',
    'latitude',
    'longitude',
  ],
  currency: ['currency', 'symbolPlacement'],
  taxes: ['taxRate', 'taxId', 'taxIncludedInPrice', 'chargeSalesTax'],
  receipts: [
    'receiptHeader',
    'receiptFooter',
    'receiptShowLogo',
    'receiptShowAddress',
    'receiptShowTax',
    'autoPrintReceipt',
  ],
  storefront: [
    'onlineStoreEnabled',
    'storeSlug',
    'publicUrl',
    'storeBio',
    'storeAccentColor',
    'allowDelivery',
    'allowPickup',
    'allowDineIn',
    'dineInTableCount',
    'deliveryFee',
    'freeDeliveryThreshold',
    'minOrderAmount',
    'whatsappOrderingEnabled',
    'whatsappDeliveryFallback',
    'fawranEnabled',
    'fawranAlias',
    'fawranMobile',
    'fawranIban',
    'showOutOfStockOnline',
    'productCardLayout',
    'showStoreHeader',
    'showStoreGradient',
    'storeNavbarColor',
  ],
};

/** Given a merged accountInfo + the changed partial, return [group, payload] pairs to PUT. */
export function settingsGroupPayloads(
  merged: Account,
  changed: Partial<Account>,
): Array<[string, Record<string, unknown>]> {
  const changedKeys = Object.keys(changed) as (keyof Account)[];
  const out: Array<[string, Record<string, unknown>]> = [];

  for (const [group, keys] of Object.entries(GROUP_KEYS)) {
    if (!keys.some((k) => changedKeys.includes(k))) continue;
    out.push([group, buildGroupPayload(group, merged)]);
  }
  return out;
}

function buildGroupPayload(group: string, a: Account): Record<string, unknown> {
  switch (group) {
    case 'general':
      return {
        business_name: a.businessName,
        tagline: a.tagline,
        phone: a.phone,
        whatsapp: a.whatsapp,
        language: a.language,
      };
    case 'business':
      return {
        owner_name: a.ownerName,
        email: a.email,
        address: a.address,
        city: a.city,
        state: a.state,
        zip_code: a.zipCode,
        country: a.country,
        latitude: (a as any).latitude ?? null,
        longitude: (a as any).longitude ?? null,
      };
    case 'currency':
      return { currency: a.currency, symbol_placement: a.symbolPlacement };
    case 'taxes':
      return {
        charge_sales_tax: (a as any).chargeSalesTax ?? true,
        tax_rate: Number(a.taxRate),
        tax_id: a.taxId,
        tax_included_in_price: !!a.taxIncludedInPrice,
      };
    case 'receipts':
      return {
        header: a.receiptHeader,
        footer: a.receiptFooter,
        show_logo: !!a.receiptShowLogo,
        show_address: !!a.receiptShowAddress,
        show_tax: !!a.receiptShowTax,
        auto_print: !!a.autoPrintReceipt,
      };
    case 'storefront':
      return {
        online_store_enabled: !!a.onlineStoreEnabled,
        store_slug: a.storeSlug,
        public_url: a.publicUrl || null,
        store_bio: a.storeBio,
        accent_color: a.storeAccentColor,
        allow_delivery: !!a.allowDelivery,
        allow_pickup: !!a.allowPickup,
        allow_dine_in: !!(a as any).allowDineIn,
        dine_in_table_count: Number((a as any).dineInTableCount ?? 0),
        delivery_fee: Number(a.deliveryFee),
        free_delivery_threshold: Number(a.freeDeliveryThreshold),
        min_order_amount: Number(a.minOrderAmount),
        whatsapp_ordering_enabled: !!a.whatsappOrderingEnabled,
        whatsapp_delivery_fallback: !!(a as any).whatsappDeliveryFallback,
        fawran_enabled: !!a.fawranEnabled,
        fawran_alias: a.fawranAlias || null,
        fawran_mobile: a.fawranMobile || null,
        fawran_iban: a.fawranIban || null,
        show_out_of_stock_online: !!a.showOutOfStockOnline,
        product_card_layout: a.productCardLayout === 'horizontal' ? 'horizontal' : 'vertical',
        show_store_header: a.showStoreHeader ?? true,
        show_store_gradient: a.showStoreGradient ?? true,
        navbar_color: a.storeNavbarColor ?? '#17201e',
      };
    default:
      return {};
  }
}

/* ---------- frontend -> API payloads ---------- */

export function productPayload(
  p: Partial<Product>,
  categories: Category[],
): Record<string, unknown> {
  const categoryId = categories.find((c) => c.name === p.category)?.id;
  return {
    name: p.name,
    name_ar: p.nameAr || null,
    description: p.description || null,
    description_ar: p.descriptionAr || null,
    price: p.price,
    cost: p.cost ?? 0,
    category_id: categoryId ? Number(categoryId) : null,
    sku: p.code || null,
    unit: p.unit || 'pcs',
    taxable: p.taxable ?? true,
    track_stock: p.trackStock ?? true,
    stock: p.stock ?? 0,
    min_stock: p.minStock ?? 0,
    show_in_online_store: p.showInOnlineStore ?? true,
    is_featured: p.isFeatured ?? false,
    variant_groups: p.variantGroups ?? [],
    additional_images: p.additionalImagePaths ?? [],
    image_focus: p.imageFocus ?? null,
    variants: (p.variants ?? []).map((v) => ({
      ...(Number.isFinite(Number(v.id)) ? { id: Number(v.id) } : {}),
      name: v.name,
      option_values: v.optionValues,
      price: v.price,
      stock: v.stock,
      sku: v.code || null,
      is_active: v.isActive,
    })),
  };
}

export function customerPayload(c: Partial<Customer>): Record<string, unknown> {
  return {
    name: c.name,
    phone: c.phone,
    email: c.email || null,
    address: c.address || null,
    address_details: c.addressDetails || null,
    city: c.city || null,
    latitude: c.latitude ?? null,
    longitude: c.longitude ?? null,
    tax_id: c.taxId || null,
    notes: c.notes || null,
  };
}
