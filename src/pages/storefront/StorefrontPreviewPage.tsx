import React, { useEffect, useMemo, useRef, useState } from 'react';
import { useParams, useSearchParams, useNavigate } from 'react-router-dom';
import { publicApi } from '../../api/endpoints';
import { useProductStore } from '../../stores/productStore';
import { useSettingsStore } from '../../stores/settingsStore';
import { useToastStore } from '../../stores/toastStore';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon } from '../../components/icons/AppIcon';
import type { IconName } from '../../components/icons/AppIcon';
import { TextInput } from '../../components/forms/TextInput';
import { LocationPicker } from '../../components/forms/LocationPicker';
import { Category, Product, ProductVariant } from '../../data/mockData';
import { toCategory, toProduct } from '../../api/adapters';
import { savePendingOrder, getPendingOrder, clearPendingOrder } from '../../utils/pendingOrder';
import type { PendingOrder } from '../../utils/pendingOrder';

type BagLine = { product: Product; variant?: ProductVariant; quantity: number };
type FulfillmentMode = 'delivery' | 'pickup' | 'dine_in';
type PublicPaymentMethod = 'WHATSAPP' | 'FAWRAN' | 'CASH';

const labels = {
  en: {
    catalog: 'Online Catalog', bag: 'Bag', add: 'Add to Bag', checkout: 'Checkout',
    search: 'Search products', all: 'All', collection: 'Our collection', products: 'products',
    viewBag: 'View bag', total: 'Total', close: 'Close',
    fulfillment: 'How would you like your order?', where: 'Where should we deliver?',
    pinHelp: 'Search or tap the map to pin your exact location.', addressFound: 'Address',
    addressDetails: 'Building, floor, apartment or nearby landmark', contact: 'Who should we contact?',
    contactHelp: 'We only use this to coordinate your order.', paymentHelp: 'Choose how you want to complete payment.',
    details: 'Your details', name: 'Full Name', phone: 'Phone Number', address: 'Delivery Address',
    city: 'City / Area', delivery: 'Delivery', pickup: 'Pickup', dineIn: 'Dine-in', payment: 'Payment Method',
    table: 'Table', tableNumber: 'Table number', tableHelp: 'This order will be sent to the staff with your table number.', chooseTable: 'Choose your table', tableLocked: 'You scanned this table QR code.',
    whatsapp: 'WhatsApp', fawran: 'Fawran Transfer', cashAtTable: 'Pay at table', cashAtTableHelp: 'A staff member will collect payment at your table.', fawranDescription: 'Transfer the total to the Fawran account below. Your name is the transfer reference. Upload your receipt to complete the order.', fawranAlias: 'Alias', fawranIban: 'IBAN', fawranReferenceNote: 'Reference: your name (automatically inserted)', reference: 'Reference', shareOrder: 'Share order on WhatsApp', shareOrderHelp: 'Send your order details to the restaurant via WhatsApp.', trackOrder: 'Track your order', trackOrderHelp: 'You will be redirected automatically in {{seconds}} seconds.', shareThenTrack: 'Share on WhatsApp, then track your order below.',
    proof: 'Payment Receipt (optional)', place: 'Place Order', confirmation: 'Order confirmed',
    location: 'Delivery Location', options: 'Choose options', continue: 'Add selected item',
    empty: 'Your bag is empty', back: 'Continue shopping', customerInfo: 'Your information', customerInfoHelp: 'Enter your phone number to auto-fill your details.',
  },
  ar: {
    catalog: 'الكتالوج الإلكتروني', bag: 'السلة', add: 'أضف إلى السلة', checkout: 'إتمام الطلب',
    search: 'ابحث عن المنتجات', all: 'الكل', collection: 'تشكيلتنا', products: 'منتجات',
    viewBag: 'عرض السلة', total: 'الإجمالي', close: 'إغلاق',
    fulfillment: 'كيف تود استلام طلبك؟', where: 'أين تريد توصيل الطلب؟',
    pinHelp: 'ابحث أو اضغط على الخريطة لتحديد موقعك بدقة.', addressFound: 'العنوان',
    addressDetails: 'المبنى، الطابق، الشقة أو أقرب معلم', contact: 'كيف نتواصل معك؟',
    contactHelp: 'نستخدم هذه البيانات فقط لتنسيق طلبك.', paymentHelp: 'اختر طريقة إتمام الدفع.',
    details: 'بياناتك', name: 'الاسم الكامل', phone: 'رقم الهاتف', address: 'عنوان التوصيل',
    city: 'المدينة / المنطقة', delivery: 'توصيل', pickup: 'استلام', dineIn: 'داخل المطعم', payment: 'طريقة الدفع',
    table: 'الطاولة', tableNumber: 'رقم الطاولة', tableHelp: 'سيصل الطلب للموظفين مع رقم طاولتك.', chooseTable: 'اختر طاولتك', tableLocked: 'تم فتح الطلب من رمز هذه الطاولة.',
    whatsapp: 'واتساب', fawran: 'تحويل فوراً', cashAtTable: 'الدفع على الطاولة', cashAtTableHelp: 'سيقوم الموظف بتحصيل الدفع على طاولتك.', fawranDescription: 'يرجى تحويل المبلغ الإجمالي إلى حساب فوران أدناه. اسمك هو مرجع التحويل. ارفع إيصال الدفع لإتمام الطلب.', fawranAlias: 'الاسم المستعار', fawranIban: 'رقم الآيبان', fawranReferenceNote: 'المرجع: اسمك (يُدرج تلقائياً)', reference: 'المرجع', shareOrder: 'مشاركة الطلب على واتساب', shareOrderHelp: 'أرسل تفاصيل طلبك إلى المطعم عبر واتساب.', trackOrder: 'تتبع طلبك', trackOrderHelp: 'سيتم تحويلك تلقائياً خلال {{seconds}} ثانية.', shareThenTrack: 'شارك عبر واتساب، ثم تتبع طلبك أدناه.',
    proof: 'إيصال الدفع (اختياري)', place: 'تأكيد الطلب', confirmation: 'تم تأكيد الطلب',
    location: 'موقع التوصيل', options: 'اختر المواصفات', continue: 'إضافة المنتج المحدد',
    empty: 'السلة فارغة', back: 'متابعة التسوق', customerInfo: 'معلوماتك', customerInfoHelp: 'أدخل رقم هاتفك لملء بياناتك تلقائياً.',
  },
};

export const StorefrontPreviewPage: React.FC = () => {
  const { slug } = useParams();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const localProducts = useProductStore((state) => state.products);
  const localSettings = useSettingsStore((state) => state.accountInfo);
  const { fetchSettings } = useSettingsStore();
  const { fetchProducts, fetchCategories } = useProductStore();
  const { addToast } = useToastStore();

  const [language, setLanguage] = useState<'en' | 'ar'>('en');
  const t = labels[language];
  const [store, setStore] = useState<any>(null);
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [search, setSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('ALL');
  const [bag, setBag] = useState<BagLine[]>([]);
  const [selecting, setSelecting] = useState<Product | null>(null);
  const [selectedOptions, setSelectedOptions] = useState<Record<string, string>>({});
  const [activePhotoIdx, setActivePhotoIdx] = useState(0);

  const productPhotos = useMemo(() => {
    if (!selecting) return [];
    const list: string[] = [];
    if (selecting.imageUrl) list.push(selecting.imageUrl);
    if (selecting.additionalImages && selecting.additionalImages.length > 0) {
      list.push(...selecting.additionalImages);
    }
    return list;
  }, [selecting]);

  const activeFocus = useMemo(() => {
    if (!selecting) return '50% 50%';
    if (activePhotoIdx === 0) {
      return selecting.imageFocus?.main ?? '50% 50%';
    }
    return selecting.imageFocus?.additional?.[activePhotoIdx - 1] ?? '50% 50%';
  }, [selecting, activePhotoIdx]);

  useEffect(() => {
    setActivePhotoIdx(0);
  }, [selecting?.id]);
  const [checkout, setCheckout] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [confirmation, setConfirmation] = useState<any>(null);
  const [redirectSeconds, setRedirectSeconds] = useState(5);
  const [pendingOrder, setPendingOrder] = useState<PendingOrder | null>(getPendingOrder);
  const [customer, setCustomer] = useState({ name: '', phone: '', address: '', addressDetails: '', city: '', latitude: undefined as number | undefined, longitude: undefined as number | undefined });
  const [fulfillment, setFulfillment] = useState<FulfillmentMode>('delivery');
  const [tableNumber, setTableNumber] = useState<number | undefined>(undefined);
  const [paymentMethod, setPaymentMethod] = useState<PublicPaymentMethod>('FAWRAN');
  const [reference, setReference] = useState('');
  const [proof, setProof] = useState<File | null>(null);
  const [lookupLoading, setLookupLoading] = useState(false);
  const [statusChangedAt, setStatusChangedAt] = useState<number | null>(null);
  const [currentStatus, setCurrentStatus] = useState<string | null>(null);
  const cartButtonRef = useRef<HTMLButtonElement>(null);
  const headerRef = useRef<HTMLElement>(null);
  const lastStatusRef = useRef<string | null>(null);
  const lookupTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const flyToCart = (sourceImage: HTMLElement | null) => {
    if (!sourceImage || !cartButtonRef.current) return;
    const cartRect = cartButtonRef.current.getBoundingClientRect();
    const sourceRect = sourceImage.getBoundingClientRect();

    const clone = sourceImage.cloneNode(true) as HTMLElement;
    clone.style.position = 'fixed';
    clone.style.left = `${sourceRect.left}px`;
    clone.style.top = `${sourceRect.top}px`;
    clone.style.width = `${sourceRect.width}px`;
    clone.style.height = `${sourceRect.height}px`;
    clone.style.borderRadius = '12px';
    clone.style.zIndex = '9999';
    clone.style.objectFit = 'cover';
    clone.style.pointerEvents = 'none';
    document.body.appendChild(clone);

    const deltaX = cartRect.left + cartRect.width / 2 - sourceRect.left - sourceRect.width / 2;
    const deltaY = cartRect.top + cartRect.height / 2 - sourceRect.top - sourceRect.height / 2;

    const animation = clone.animate(
      [
        { transform: 'scale(1)', opacity: 1 },
        { transform: `translate(${deltaX}px, ${deltaY}px) scale(0.15)`, opacity: 0.5 },
      ],
      { duration: 550, easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)' }
    );

    animation.onfinish = () => clone.remove();
  };

  const copyToClipboard = async (value: string, label: string) => {
    try {
      await navigator.clipboard.writeText(value);
      addToast(`${label} copied!`, 'success');
    } catch {
      addToast('Failed to copy', 'danger');
    }
  };

  useEffect(() => {
    if (!slug) {
      if (!localSettings.businessName) {
        fetchSettings();
        fetchProducts();
        fetchCategories();
        return;
      }
      setStore({
        business_name: localSettings.businessName, phone: localSettings.phone, currency: localSettings.currency,
        currency_symbol: localSettings.currencySymbol, symbol_placement: localSettings.symbolPlacement,
        bio: localSettings.storeBio, banner_url: localSettings.bannerUrl, logo_url: localSettings.logoUrl,
        accent_color: localSettings.storeAccentColor, allow_delivery: localSettings.allowDelivery,
        allow_pickup: localSettings.allowPickup, allow_dine_in: (localSettings as any).allowDineIn,
        dine_in_table_count: (localSettings as any).dineInTableCount ?? 0,
        whatsapp_ordering_enabled: localSettings.whatsappOrderingEnabled,
        fawran_enabled: localSettings.fawranEnabled, fawran_alias: localSettings.fawranAlias,
        fawran_mobile: localSettings.fawranMobile, fawran_iban: localSettings.fawranIban,
        product_card_layout: localSettings.productCardLayout || 'vertical',
        show_store_header: localSettings.showStoreHeader ?? true,
        show_store_gradient: localSettings.showStoreGradient ?? true,
        secondary_color: localSettings.storeSecondaryColor ?? '#373f4e',
        navbar_color: localSettings.storeNavbarColor ?? '#17201e',
      });
      setProducts(localProducts.filter((product) => product.showInOnlineStore));
      setCategories(useProductStore.getState().categories);
      return;
    }
    Promise.all([publicApi.store(slug), publicApi.catalog(slug)]).then(([storeResponse, catalogResponse]: any[]) => {
      setStore(storeResponse.data);
      setProducts((catalogResponse.data?.products ?? []).map(toProduct));
      setCategories((catalogResponse.data?.categories ?? []).map(toCategory));
    });
  }, [slug, localProducts, localSettings]);

  // Auto-redirect customer to the live order-status page after confirmation.
  useEffect(() => {
    if (!confirmation?.order_number) return;
    const effectiveSlug = slug || localSettings.storeSlug;
    if (!effectiveSlug) return;

    setRedirectSeconds(5);
    const countdownId = setInterval(() => {
      setRedirectSeconds((current) => (current > 0 ? current - 1 : 0));
    }, 1000);

    const redirectId = setTimeout(() => {
      navigate(`/store/${effectiveSlug}/orders/${confirmation.order_number}`);
    }, 5000);

    return () => {
      clearInterval(countdownId);
      clearTimeout(redirectId);
    };
  }, [confirmation, slug, localSettings.storeSlug, navigate]);

  // Poll pending order status; vibrate on status change.
  useEffect(() => {
    if (!pendingOrder) return;
    const poll = async () => {
      try {
        const res: any = await publicApi.getOrder(pendingOrder.slug, pendingOrder.orderNumber);
        const status: string = res?.data?.status;
        if (status) {
          if (lastStatusRef.current && lastStatusRef.current !== status) {
            setStatusChangedAt(Date.now());
            if (navigator.vibrate) navigator.vibrate([120, 80, 120, 80, 200]);
          }
          setCurrentStatus(status);
          lastStatusRef.current = status;
          if (status === 'COMPLETE' || status === 'CANCELLED') {
            clearPendingOrder();
            setPendingOrder(null);
            setCurrentStatus(null);
          }
        }
      } catch {}
    };
    poll();
    const id = setInterval(poll, 15_000);
    return () => clearInterval(id);
  }, [pendingOrder]);

  useEffect(() => {
    if (!statusChangedAt) return;
    const id = setTimeout(() => setStatusChangedAt(null), 6000);
    return () => clearTimeout(id);
  }, [statusChangedAt]);

  useEffect(() => {
    if (!store) return;

    const requestedMode = searchParams.get('mode');
    const requestedTable = Number(searchParams.get('table'));
    const hasTableQr = Number.isFinite(requestedTable) && requestedTable > 0;

    if ((requestedMode === 'dine_in' || hasTableQr) && store.allow_dine_in) {
      setFulfillment('dine_in');
      setPaymentMethod('CASH');
      if (hasTableQr) setTableNumber(requestedTable);
      return;
    }

    if (fulfillment === 'delivery' && !store.allow_delivery) {
      setFulfillment(store.allow_pickup ? 'pickup' : store.allow_dine_in ? 'dine_in' : 'delivery');
    } else if (fulfillment === 'pickup' && !store.allow_pickup) {
      setFulfillment(store.allow_delivery ? 'delivery' : store.allow_dine_in ? 'dine_in' : 'pickup');
    } else if (fulfillment === 'dine_in' && !store.allow_dine_in) {
      setFulfillment(store.allow_delivery ? 'delivery' : store.allow_pickup ? 'pickup' : 'dine_in');
    }
  }, [store, searchParams, fulfillment]);

  useEffect(() => {
    const updateHeaderHeight = () => {
      if (headerRef.current) {
        const height = headerRef.current.getBoundingClientRect().height;
        document.documentElement.style.setProperty('--shopbar-height', `${height}px`);
      }
    };
    updateHeaderHeight();
    let resizeObserver: ResizeObserver | null = null;
    if (headerRef.current && typeof ResizeObserver !== 'undefined') {
      resizeObserver = new ResizeObserver(updateHeaderHeight);
      resizeObserver.observe(headerRef.current);
    }
    window.addEventListener('resize', updateHeaderHeight);
    return () => {
      if (resizeObserver) resizeObserver.disconnect();
      window.removeEventListener('resize', updateHeaderHeight);
    };
  }, []);

  const money = (value: number) => {
    const symbol = store?.currency === 'QAR' ? 'QAR' : (store?.currency_symbol ?? store?.currency ?? '');
    const amount = value.toFixed(2);
    return store?.symbol_placement === 'AFTER' ? `${amount} ${symbol}` : `${symbol} ${amount}`;
  };

  const total = bag.reduce((sum, line) => sum + (line.variant?.price ?? line.product.price) * line.quantity, 0);
  const itemCount = bag.reduce((sum, line) => sum + line.quantity, 0);
  const tableCount = Math.max(0, Number(store?.dine_in_table_count ?? 0));
  const tableLockedFromQr = Number(searchParams.get('table')) > 0;
  const tableValid = fulfillment !== 'dine_in' || (tableNumber != null && tableNumber > 0 && (tableCount === 0 || tableNumber <= tableCount));
  const canSubmit = !!bag.length
    && !!customer.name
    && !!customer.phone
    && (fulfillment !== 'delivery' || (customer.latitude != null && customer.longitude != null && !!customer.address))
    && tableValid;

  const chooseFulfillment = (mode: FulfillmentMode) => {
    setFulfillment(mode);
    if (mode === 'dine_in') {
      setPaymentMethod('CASH');
      setTableNumber((current) => current ?? (tableCount > 0 ? 1 : undefined));
    } else if (paymentMethod === 'CASH') {
      setPaymentMethod('FAWRAN');
    }
  };
  const filteredProducts = useMemo(() => products.filter((product) => {
    const localizedName = language === 'ar' && product.nameAr ? product.nameAr : product.name;
    return (selectedCategory === 'ALL' || product.category === selectedCategory)
      && localizedName.toLowerCase().includes(search.trim().toLowerCase());
  }), [products, language, search, selectedCategory]);
  const groupedProducts = useMemo(() => {
    const groups = new Map<string, Product[]>();
    filteredProducts.forEach((product) => {
      if (!groups.has(product.category)) groups.set(product.category, []);
      groups.get(product.category)!.push(product);
    });
    const categoryOrder = new Map(categories.map((c, i) => [c.name, i]));
    return Array.from(groups.entries()).sort((a, b) => {
      const orderA = categoryOrder.get(a[0]) ?? Infinity;
      const orderB = categoryOrder.get(b[0]) ?? Infinity;
      return orderA - orderB;
    });
  }, [filteredProducts, categories]);
  const selectedVariant = useMemo(() => selecting?.variants.find((variant) =>
    variant.isActive
    && Object.entries(selectedOptions).every(([groupId, optionId]) => variant.optionValues[groupId] === optionId)
    && Object.entries(variant.optionValues).every(([groupId, optionId]) => selectedOptions[groupId] === optionId)
  ), [selecting, selectedOptions]);

  const addLine = (product: Product, variant?: ProductVariant) => {
    const key = `${product.id}:${variant?.id ?? 'base'}`;
    setBag((current) => {
      const exists = current.find((line) => `${line.product.id}:${line.variant?.id ?? 'base'}` === key);
      return exists
        ? current.map((line) => `${line.product.id}:${line.variant?.id ?? 'base'}` === key ? { ...line, quantity: line.quantity + 1 } : line)
        : [...current, { product, variant, quantity: 1 }];
    });
    setSelecting(null);
    setSelectedOptions({});
  };

  const changeQuantity = (line: BagLine, delta: number) => {
    const key = `${line.product.id}:${line.variant?.id ?? 'base'}`;
    setBag((current) => current
      .map((item) => `${item.product.id}:${item.variant?.id ?? 'base'}` === key
        ? { ...item, quantity: item.quantity + delta }
        : item)
      .filter((item) => item.quantity > 0));
  };

  const handlePhoneLookup = (phone: string) => {
    setCustomer((current) => ({ ...current, phone }));
    if (lookupTimerRef.current) clearTimeout(lookupTimerRef.current);
    const digits = phone.replace(/\D/g, '');
    if (digits.length < 6) return;
    const effectiveSlug = slug || localSettings.storeSlug;
    if (!effectiveSlug) return;
    lookupTimerRef.current = setTimeout(async () => {
      setLookupLoading(true);
      try {
        const res: any = await publicApi.lookupCustomer(effectiveSlug, phone);
        const data = res?.data;
        if (data) {
          const lat = data.latitude != null ? Number(data.latitude) : undefined;
          const lng = data.longitude != null ? Number(data.longitude) : undefined;
          setCustomer({
            name: data.name ?? '',
            phone: data.phone ?? phone,
            address: data.address ?? '',
            addressDetails: data.address_details ?? '',
            city: data.city ?? '',
            latitude: lat != null && !Number.isNaN(lat) ? lat : undefined,
            longitude: lng != null && !Number.isNaN(lng) ? lng : undefined,
          });
        }
      } catch {} finally {
        setLookupLoading(false);
      }
    }, 600);
  };

  const submitOrder = async () => {
    const effectiveSlug = slug || localSettings.storeSlug;
    if (!effectiveSlug || !canSubmit) return;
    setSubmitting(true);
    try {
      const form = new FormData();
      form.append('customer[name]', customer.name);
      form.append('customer[phone]', customer.phone);
      form.append('customer[address]', customer.address);
      form.append('customer[address_details]', customer.addressDetails);
      form.append('customer[city]', customer.city);
      if (customer.latitude != null) form.append('customer[latitude]', String(customer.latitude));
      if (customer.longitude != null) form.append('customer[longitude]', String(customer.longitude));
      form.append('fulfillment', fulfillment);
      if (fulfillment === 'dine_in' && tableNumber != null) form.append('table_number', String(tableNumber));
      form.append('payment_method', paymentMethod);

      if (proof) form.append('payment_proof', proof);
      bag.forEach((line, index) => {
        form.append(`items[${index}][product_id]`, line.product.id);
        form.append(`items[${index}][quantity]`, String(line.quantity));
        if (line.variant) form.append(`items[${index}][variant_id]`, line.variant.id);
      });
      const response: any = await publicApi.createOrder(effectiveSlug, form);
      setConfirmation(response.data);
      const orderNumber = response.data?.order_number;
      if (orderNumber) {
        savePendingOrder({ orderNumber, slug: effectiveSlug });
        setPendingOrder({ orderNumber, slug: effectiveSlug });
      }
    } catch (err: any) {
      const message = err?.message || 'Something went wrong. Try again.';
      addToast(message, 'danger');
    } finally {
      setSubmitting(false);
    }
  };

  if (!store) return <div className="public-store-loading">Loading store…</div>;

  const statusIcon = (status: string | null): IconName => {
    switch (status) {
      case 'ACCEPTED': return 'checkCircle';
      case 'PREPARING': return 'flame';
      case 'OUT_FOR_DELIVERY': return 'delivery';
      case 'COMPLETE': return 'check';
      case 'CANCELLED': return 'close';
      default: return 'clock';
    }
  };

  return (
    <div className="public-store" dir={language === 'ar' ? 'rtl' : 'ltr'} style={{
      '--store-accent': store.accent_color ?? '#0f766e',
      '--store-secondary': store.secondary_color ?? '#373f4e',
      '--store-navbar': store.navbar_color ?? '#17201e',
    } as React.CSSProperties}>
      <header ref={headerRef} className="mobile-shopbar">
        <div className="store-identity">{store.logo_url ? <img src={store.logo_url} alt="" /> : <span>{store.business_name[0]}</span>}<div><h1>{store.business_name}</h1><p>{t.catalog}</p></div></div>
        <div className="shopbar-actions">
          {!checkout && !confirmation && pendingOrder && (
            <button className={`status-toggle-button ${statusChangedAt != null ? 'status-changed' : ''}`} onClick={() => navigate(`/store/${pendingOrder.slug}/orders/${pendingOrder.orderNumber}`)} aria-label="Order status">
              <AppIcon name={statusIcon(currentStatus)} size={20} />
              <span className="status-toggle-dot" aria-hidden="true" />
            </button>
          )}
          <button ref={cartButtonRef} className={`bag-icon-button ${itemCount === 0 ? 'bag-empty' : ''}`} onClick={() => itemCount > 0 && setCheckout(true)} disabled={itemCount === 0} aria-label={`${t.bag}, ${itemCount} ${t.products}`}><AppIcon name="cart" />{itemCount > 0 && <span>{itemCount}</span>}</button>
          <button className="language-toggle" onClick={() => setLanguage(language === 'en' ? 'ar' : 'en')} aria-label="Change language">{language === 'en' ? 'ع' : 'EN'}</button>
        </div>
      </header>

      {(store.banner_url || store.show_store_gradient || store.show_store_header) && (
        <section
          className="store-hero"
          style={store.banner_url
            ? store.show_store_gradient
              ? { backgroundImage: `linear-gradient(90deg, color-mix(in srgb, var(--store-accent) 78%, transparent), color-mix(in srgb, var(--store-secondary) 28%, transparent)), url(${store.banner_url})` }
              : { backgroundImage: `url(${store.banner_url})` }
            : store.show_store_gradient
              ? undefined
              : { background: 'transparent', color: 'inherit', minHeight: 'auto', paddingBlock: '18px' }
          }
        >
          {store.show_store_header && (
            <div>
              <span className="hero-eyebrow">{store.allow_dine_in ? t.dineIn : store.allow_delivery ? t.delivery : t.pickup}</span>
              <h2 style={!store.show_store_gradient && !store.banner_url ? { color: '#17201e' } : undefined}>{store.bio || t.collection}</h2>
            </div>
          )}
        </section>
      )}

      <main className="store-content">
        {!checkout && !confirmation && <>
          <section className="catalog-toolbar">
            <label className="store-search">
              <AppIcon name="search" />
              <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder={t.search} aria-label={t.search} />
              <span className="store-search-count">{filteredProducts.length} {t.products}</span>
            </label>
          </section>
          <div className="store-categories" aria-label="Product categories">
            <button className={selectedCategory === 'ALL' ? 'selected' : ''} onClick={() => setSelectedCategory('ALL')}><span className="category-thumb category-thumb-all"><AppIcon name="home" size={28} /></span><span>{t.all}</span></button>
            {categories.map((category) => <button key={category.id} className={selectedCategory === category.name ? 'selected' : ''} onClick={() => setSelectedCategory(category.name)}>{category.imageUrl && <img src={category.imageUrl} loading="lazy" alt="" />}<span>{category.name}</span></button>)}
          </div>
          {groupedProducts.map(([categoryName, categoryProducts]) => (
            <section className="store-category-section" key={categoryName}>
              <h3 className="store-category-heading">{categoryName}</h3>
              <div className={`store-grid ${store.product_card_layout === 'horizontal' ? 'store-grid-horizontal' : ''}`}>
                {categoryProducts.map((product) => (
                  <article className="store-product-card" key={product.id}>
                    <div className="store-product-image">{product.imageUrl ? <img src={product.imageUrl} loading="lazy" width="320" height="320" alt={language === 'ar' && product.nameAr ? product.nameAr : product.name} style={{ objectPosition: product.imageFocus?.main ?? '50% 50%' }} /> : <AppIcon name="products" size={36} />}</div>
                    <div className="store-product-copy"><p>{product.category}</p><h2>{language === 'ar' && product.nameAr ? product.nameAr : product.name}</h2><span>{language === 'ar' && product.descriptionAr ? product.descriptionAr : product.description}</span>{product.variants.length > 0 && <small className="variant-hint">{t.options}</small>}</div>
                    <div className="store-product-action"><strong>{money(product.price)}</strong><button className="mobile-add-button" aria-label={`${t.add}: ${product.name}`} onClick={(e) => {
                      if (product.variants.length) {
                        setSelecting(product);
                      } else {
                        const card = (e.currentTarget as HTMLElement).closest('.store-product-card');
                        const img = card?.querySelector('.store-product-image img') as HTMLElement | null;
                        flyToCart(img);
                        addLine(product);
                      }
                    }}><AppIcon name="plus" /></button></div>
                  </article>
                ))}
              </div>
            </section>
          ))}
          {filteredProducts.length === 0 && <div className="store-empty"><AppIcon name="search" size={34} /><p>No products found</p></div>}
        </>}

        {checkout && !confirmation && <section className="store-checkout">
          <div className="checkout-main">
            <div className="mobile-checkout-header"><button aria-label={t.back} onClick={() => setCheckout(false)}><AppIcon name={language === 'ar' ? 'chevronRight' : 'chevronLeft'} /></button><div><h2>{t.checkout}</h2><p>{t.details}</p></div></div>
            <aside className="checkout-summary"><h3>{t.bag} · {itemCount}</h3>{bag.length === 0 && <p>{t.empty}</p>}{bag.map((line) => <div className="checkout-line" key={`${line.product.id}:${line.variant?.id}`}><div className="checkout-line-image">{line.product.imageUrl ? <img src={line.product.imageUrl} alt="" style={{ objectPosition: line.product.imageFocus?.main ?? '50% 50%', objectFit: 'cover' }} /> : <AppIcon name="products" />}</div><span>{language === 'ar' && line.product.nameAr ? line.product.nameAr : line.product.name}<small>{line.variant?.name}</small><span className="quantity-stepper"><button onClick={() => changeQuantity(line, -1)} aria-label="Decrease quantity">−</button><b>{line.quantity}</b><button onClick={() => changeQuantity(line, 1)} aria-label="Increase quantity">+</button></span></span><strong>{money((line.variant?.price ?? line.product.price) * line.quantity)}</strong></div>)}</aside>
            <section className="checkout-step">
              <div className="checkout-step-title"><span>1</span><div><h3>{t.customerInfo}</h3><p>{t.customerInfoHelp}</p></div></div>
              <div className="form-grid-2">
                <TextInput label={t.phone} type="tel" autoComplete="tel" value={customer.phone} onChange={(event) => handlePhoneLookup(event.target.value)} />
                <TextInput label={t.name} autoComplete="name" value={customer.name} onChange={(event) => setCustomer({ ...customer, name: event.target.value })} />
              </div>
              {lookupLoading && <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Looking up…</span>}
            </section>
            <section className="checkout-step">
              <div className="checkout-step-title"><span>2</span><div><h3>{t.fulfillment}</h3></div></div>
              <div className="choice-row checkout-choice">
                {store.allow_delivery && <button className={fulfillment === 'delivery' ? 'selected' : ''} onClick={() => chooseFulfillment('delivery')}><AppIcon name="mapPin" />{t.delivery}</button>}
                {store.allow_pickup && <button className={fulfillment === 'pickup' ? 'selected' : ''} onClick={() => chooseFulfillment('pickup')}><AppIcon name="storefront" />{t.pickup}</button>}
                {store.allow_dine_in && <button className={fulfillment === 'dine_in' ? 'selected' : ''} onClick={() => chooseFulfillment('dine_in')}><AppIcon name="dineIn" />{t.dineIn}</button>}
              </div>
            </section>
            {fulfillment === 'delivery' && <section className="checkout-step location-first"><div className="checkout-step-title"><span>3</span><div><h3>{t.where}</h3><p>{t.pinHelp}</p></div></div><LocationPicker latitude={customer.latitude} longitude={customer.longitude} onChange={(latitude, longitude) => setCustomer((current) => ({ ...current, latitude, longitude }))} onAddressResolved={(address, city) => setCustomer((current) => ({ ...current, address, city: city ?? current.city }))} /><TextInput label={t.addressFound} value={customer.address} onChange={(event) => setCustomer({ ...customer, address: event.target.value })} /><TextInput label={t.addressDetails} value={customer.addressDetails} onChange={(event) => setCustomer({ ...customer, addressDetails: event.target.value })} /></section>}
            {fulfillment === 'dine_in' && <section className="checkout-step dine-in-table-step">
              <div className="checkout-step-title"><span>3</span><div><h3>{t.table}</h3><p>{tableLockedFromQr ? t.tableLocked : t.tableHelp}</p></div></div>
              {tableLockedFromQr
                ? <div className="locked-table-card"><AppIcon name="table" size={22} /><div><span>{t.tableNumber}</span><strong>{tableNumber}</strong></div></div>
                : <label className="table-select-field"><span>{t.chooseTable}</span><select value={tableNumber ?? ''} onChange={(event) => setTableNumber(event.target.value ? Number(event.target.value) : undefined)}>{!tableNumber && <option value="">{t.chooseTable}</option>}{Array.from({ length: tableCount }, (_, index) => index + 1).map((number) => <option value={number} key={number}>{t.table} {number}</option>)}</select></label>}
            </section>}
            <section className="checkout-step"><div className="checkout-step-title"><span>{fulfillment === 'delivery' || fulfillment === 'dine_in' ? '4' : '3'}</span><div><h3>{t.payment}</h3><p>{paymentMethod === 'CASH' ? t.cashAtTableHelp : t.paymentHelp}</p></div></div><div className="choice-row checkout-choice">{fulfillment === 'dine_in' && <button className={paymentMethod === 'CASH' ? 'selected' : ''} onClick={() => setPaymentMethod('CASH')}><AppIcon name="cash" />{t.cashAtTable}</button>}{store.fawran_enabled && <button className={paymentMethod === 'FAWRAN' ? 'selected' : ''} onClick={() => setPaymentMethod('FAWRAN')}><AppIcon name="transfer" />{t.fawran}</button>}</div>{paymentMethod === 'FAWRAN' && (
                      <div className="fawran-panel">
                        <div className="fawran-header">
                          <img className="fawran-logo-img" src="/fawran-logo.png" alt="Fawran" />
                          <p>{t.fawranDescription}</p>
                        </div>
                        <div className="fawran-details">
                          {store.fawran_mobile && (
                            <button type="button" className="fawran-copy-row" onClick={() => copyToClipboard(store.fawran_mobile, t.fawranAlias)}>
                              <span>{t.fawranAlias}</span>
                              <strong>{store.fawran_mobile}</strong>
                              <AppIcon name="copy" size={16} />
                            </button>
                          )}
                          {customer.name && (
                            <button type="button" className="fawran-copy-row" onClick={() => copyToClipboard(customer.name, t.reference)}>
                              <span>{t.reference}</span>
                              <strong>{customer.name}</strong>
                              <AppIcon name="copy" size={16} />
                            </button>
                          )}
                        </div>
                        <label className="file-picker">{t.proof}<input type="file" accept="image/jpeg,image/png,image/webp" onChange={(event) => setProof(event.target.files?.[0] ?? null)} /></label>
                      </div>
                    )}</section>
            <div className="checkout-final-action"><div><span>{t.total}</span><strong>{money(total)}</strong></div><AppButton variant="primary" loading={submitting} disabled={!canSubmit} onClick={submitOrder}>{t.place}</AppButton></div>
          </div>
        </section>}

        {confirmation && <section className="order-confirmation"><div className="success-mark"><AppIcon name="check" size={34} /></div><h2>{t.confirmation}</h2><p>{confirmation.order_number}</p>{confirmation.table_number && <p>{t.table} {confirmation.table_number}</p>}<strong>{money(Number(confirmation.total))}</strong><StatusText value={confirmation.payment_status} /><p className="confirmation-help">{t.shareThenTrack}</p>{confirmation.whatsapp_url && <a className="whatsapp-share-button" href={confirmation.whatsapp_url} target="_blank" rel="noopener noreferrer"><AppIcon name="whatsapp" size={18} />{t.shareOrder}</a>}<AppButton variant="primary" onClick={() => {
              const effectiveSlug = slug || localSettings.storeSlug;
              if (effectiveSlug && confirmation.order_number) {
                navigate(`/store/${effectiveSlug}/orders/${confirmation.order_number}`);
              }
            }}>{t.trackOrder}</AppButton><p className="confirmation-countdown">{t.trackOrderHelp.replace('{{seconds}}', String(redirectSeconds))}</p></section>}
      </main>

      {bag.length > 0 && !checkout && !confirmation && <button className="mobile-cart-dock" onClick={() => setCheckout(true)}><span className="cart-dock-count">{itemCount}</span><span><b>{t.viewBag}</b><small>{money(total)}</small></span><AppIcon name={language === 'ar' ? 'chevronLeft' : 'chevronRight'} /></button>}

      {selecting && <div className="public-dialog-backdrop" onClick={() => setSelecting(null)}><div className="public-dialog" onClick={(event) => event.stopPropagation()}><div className="sheet-grabber" /><div className="sheet-product-new" style={{ display: 'flex', flexDirection: 'column', gap: '14px', marginBottom: '20px', position: 'relative' }}><button aria-label={t.close} onClick={() => setSelecting(null)} style={{ position: 'absolute', top: '0', right: '0', background: 'rgba(0,0,0,0.05)', border: 'none', borderRadius: '50%', width: '32px', height: '32px', display: 'grid', placeItems: 'center', cursor: 'pointer', zIndex: 10 }}><AppIcon name="close" /></button><div className="sheet-product-gallery" style={{ width: '100%', display: 'flex', flexDirection: 'column', gap: '8px', alignItems: 'center' }}><div style={{ width: '100%', aspectRatio: '4/3', borderRadius: '16px', overflow: 'hidden', background: '#f8fafc', border: '1px solid #f1f5f9', display: 'grid', placeItems: 'center' }}>{productPhotos.length > 0 ? <img src={productPhotos[activePhotoIdx]} alt="" style={{ width: '100%', height: '100%', objectFit: 'contain', objectPosition: activeFocus }} /> : <AppIcon name="products" size={64} />}</div>{productPhotos.length > 1 && <div style={{ display: 'flex', gap: '8px', overflowX: 'auto', padding: '4px 0', maxWidth: '100%', justifyContent: 'center' }}>{productPhotos.map((url, idx) => {
                  const thumbFocus = idx === 0 
                    ? (selecting.imageFocus?.main ?? '50% 50%')
                    : (selecting.imageFocus?.additional?.[idx - 1] ?? '50% 50%');
                  return (
                    <button key={idx} type="button" onClick={() => setActivePhotoIdx(idx)} style={{ width: '44px', height: '44px', borderRadius: '8px', overflow: 'hidden', padding: '0', border: idx === activePhotoIdx ? '2px solid var(--store-accent)' : '1px solid #e2e8f0', cursor: 'pointer', background: 'none', flexShrink: 0 }}>
                      <img src={url} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover', objectPosition: thumbFocus }} />
                    </button>
                  );
                })}</div>}</div><div style={{ marginTop: '4px' }}><small style={{ fontSize: '0.75rem', color: 'var(--store-accent)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{t.options}</small><h2 style={{ fontSize: '1.25rem', fontWeight: 700, margin: '4px 0 6px', color: '#1e293b' }}>{language === 'ar' && selecting.nameAr ? selecting.nameAr : selecting.name}</h2><strong style={{ fontSize: '1.35rem', fontWeight: 800, color: 'var(--store-accent)' }}>{selectedVariant ? money(selectedVariant.price) : money(selecting.price)}</strong>{selecting.description && <p style={{ fontSize: '0.85rem', color: '#64748b', marginTop: '10px', lineHeight: 1.5 }}>{language === 'ar' && selecting.descriptionAr ? selecting.descriptionAr : selecting.description}</p>}</div></div>{selecting.variantGroups.map((group) => <fieldset key={group.id}><legend>{language === 'ar' && group.nameAr ? group.nameAr : group.name}{group.required ? ' *' : ''}</legend><div className="choice-row">{!group.required && <button className={!selectedOptions[group.id] ? 'selected' : ''} onClick={() => setSelectedOptions((current) => { const copy = { ...current }; delete copy[group.id]; return copy; })}>—</button>}{group.options.map((option) => <button key={option.id} className={selectedOptions[group.id] === option.id ? 'selected' : ''} onClick={() => setSelectedOptions({ ...selectedOptions, [group.id]: option.id })}>{language === 'ar' && option.nameAr ? option.nameAr : option.name}</button>)}</div></fieldset>)}<AppButton variant="primary" disabled={!selectedVariant || selectedVariant.stock <= 0} onClick={() => {
                      if (selectedVariant) {
                        const img = document.querySelector('.sheet-product-gallery img') as HTMLElement | null;
                        flyToCart(img);
                        addLine(selecting, selectedVariant);
                      }
                    }}>{t.continue}{selectedVariant ? ` · ${money(selectedVariant.price)}` : ''}</AppButton></div></div>}
    </div>
  );
};

const StatusText: React.FC<{ value: string }> = ({ value }) => <span className="status-pill warning">{value.replace(/_/g, ' ')}</span>;
