import React, { useEffect, useMemo, useRef, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { publicApi } from '../../api/endpoints';
import { toOrder } from '../../api/adapters';
import { AppIcon } from '../../components/icons/AppIcon';
import { Order, OrderStatus } from '../../data/mockData';
import { AppButton } from '../../components/buttons/AppButton';

const statusFlow: OrderStatus[] = [
  'CONFIRMING',
  'ACCEPTED',
  'PREPARING',
  'OUT_FOR_DELIVERY',
  'COMPLETE',
];

const labels = {
  en: {
    title: 'Order Status',
    orderNumber: 'Order',
    status: 'Status',
    estimated: 'We are preparing your order.',
    items: 'Items',
    subtotal: 'Subtotal',
    deliveryFee: 'Delivery Fee',
    tax: 'Tax',
    discount: 'Discount',
    total: 'Total',
    contact: 'Need help?',
    call: 'Call',
    whatsapp: 'WhatsApp',
    back: 'Back to menu',
    refresh: 'Refresh',
    notFound: 'Order not found.',
    loading: 'Loading order...',
    stages: {
      CONFIRMING: 'Confirming',
      ACCEPTED: 'Accepted',
      PREPARING: 'Preparing',
      OUT_FOR_DELIVERY: 'Out for delivery',
      COMPLETE: 'Completed',
      CANCELLED: 'Cancelled',
    },
    fulfillment: {
      DELIVERY: 'Delivery',
      PICKUP: 'Pickup',
      DINE_IN: 'Dine-in',
    },
    table: 'Table',
    feePending: 'Pending confirmation',
    feePendingHelp: 'The store is confirming your delivery fee — the total will update here.',
    payNow: 'Complete your payment',
    fawranPayHelp: 'Transfer the total to the Fawran account below, then upload your receipt.',
    fawranAlias: 'Alias',
    reference: 'Reference',
    uploadProof: 'Upload payment receipt',
    proofSent: 'Receipt received — the store is verifying your payment.',
    uploadFailed: 'Upload failed. Try again.',
  },
  ar: {
    title: 'حالة الطلب',
    orderNumber: 'طلب',
    status: 'الحالة',
    estimated: 'نحن نُحضّر طلبك الآن.',
    items: 'المنتجات',
    subtotal: 'المجموع',
    deliveryFee: 'رسوم التوصيل',
    tax: 'الضريبة',
    discount: 'الخصم',
    total: 'الإجمالي',
    contact: 'تحتاج مساعدة؟',
    call: 'اتصال',
    whatsapp: 'واتساب',
    back: 'العودة للقائمة',
    refresh: 'تحديث',
    notFound: 'الطلب غير موجود.',
    loading: 'جاري تحميل الطلب...',
    stages: {
      CONFIRMING: 'قيد التأكيد',
      ACCEPTED: 'تم القبول',
      PREPARING: 'قيد التحضير',
      OUT_FOR_DELIVERY: 'في الطريق',
      COMPLETE: 'مكتمل',
      CANCELLED: 'ملغى',
    },
    fulfillment: {
      DELIVERY: 'توصيل',
      PICKUP: 'استلام',
      DINE_IN: 'داخل المطعم',
    },
    table: 'طاولة',
    feePending: 'بانتظار التأكيد',
    feePendingHelp: 'يقوم المتجر بتأكيد رسوم التوصيل — سيتم تحديث الإجمالي هنا.',
    payNow: 'أكمل الدفع',
    fawranPayHelp: 'حوّل المبلغ الإجمالي إلى حساب فوران أدناه، ثم ارفع الإيصال.',
    fawranAlias: 'الاسم المستعار',
    reference: 'المرجع',
    uploadProof: 'رفع إيصال الدفع',
    proofSent: 'تم استلام الإيصال — يقوم المتجر بالتحقق من الدفع.',
    uploadFailed: 'فشل الرفع. حاول مرة أخرى.',
  },
};

export const OrderStatusPage: React.FC = () => {
  const { slug, orderNumber } = useParams<{ slug: string; orderNumber: string }>();
  const navigate = useNavigate();
  const [language, setLanguage] = useState<'en' | 'ar'>('en');
  const t = labels[language];
  const lastStatusRef = useRef<string | null>(null);

  const [store, setStore] = useState<any>(null);
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [uploading, setUploading] = useState(false);
  const [uploadError, setUploadError] = useState(false);

  const handleProofUpload = async (file: File) => {
    if (!slug || !orderNumber) return;
    setUploading(true);
    setUploadError(false);
    try {
      await publicApi.uploadOrderPaymentProof(slug, orderNumber, file);
      await fetchOrder();
    } catch {
      setUploadError(true);
    } finally {
      setUploading(false);
    }
  };

  const fetchOrder = async () => {
    if (!slug || !orderNumber) return;
    try {
      const [storeResponse, orderResponse]: [any, any] = await Promise.all([
        publicApi.store(slug),
        publicApi.getOrder(slug, orderNumber),
      ]);
      setStore(storeResponse.data);
      const mapped = toOrder(orderResponse.data);
      setOrder(mapped);
      lastStatusRef.current = mapped.status;
      setError(null);
    } catch (e: any) {
      setError(e.message ?? t.notFound);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOrder();
  }, [slug, orderNumber]);

  // Poll for live status updates every 10 seconds; vibrate on change.
  useEffect(() => {
    if (!slug || !orderNumber) return;
    const id = setInterval(() => {
      publicApi
        .getOrder(slug, orderNumber)
        .then((res: any) => {
          const updated = toOrder(res.data);
          const newStatus = updated.status;
          if (lastStatusRef.current && lastStatusRef.current !== newStatus) {
            if (navigator.vibrate) navigator.vibrate(200);
          }
          lastStatusRef.current = newStatus;
          setOrder(updated);
        })
        .catch(() => {});
    }, 10_000);
    return () => clearInterval(id);
  }, [slug, orderNumber]);

  const money = (value: number) => {
    if (!store) return value.toFixed(2);
    const symbol = store.currency_symbol ?? store.currency ?? '';
    const amount = Number(value).toFixed(2);
    return store.symbol_placement === 'AFTER' ? `${amount} ${symbol}` : `${symbol} ${amount}`;
  };

  const currentIndex = useMemo(() => {
    if (!order) return -1;
    if (order.status === 'CANCELLED') return -1;
    return statusFlow.indexOf(order.status);
  }, [order]);

  const statusMessage = useMemo(() => {
    if (!order) return '';
    if (order.status === 'CANCELLED') return t.stages.CANCELLED;
    if (order.status === 'COMPLETE') return t.stages.COMPLETE;
    if (order.status === 'OUT_FOR_DELIVERY') return t.stages.OUT_FOR_DELIVERY;
    return t.estimated;
  }, [order, t]);

  if (loading) {
    return (
      <div className="order-status-page" dir={language === 'ar' ? 'rtl' : 'ltr'}>
        <div className="order-status-loading">{t.loading}</div>
      </div>
    );
  }

  if (error || !order || !store) {
    return (
      <div className="order-status-page" dir={language === 'ar' ? 'rtl' : 'ltr'}>
        <div className="order-status-error">
          <AppIcon name="alert" size={40} />
          <p>{error || t.notFound}</p>
          <AppButton variant="secondary" onClick={() => navigate(`/store/${slug}`)}>
            {t.back}
          </AppButton>
        </div>
      </div>
    );
  }

  const whatsappUrl = store.phone ? `https://wa.me/${store.phone.replace(/\D/g, '')}` : null;
  const phoneUrl = store.phone ? `tel:${store.phone}` : null;

  return (
    <div
      className="order-status-page"
      dir={language === 'ar' ? 'rtl' : 'ltr'}
      style={
        {
          '--store-accent': store.accent_color ?? '#0f766e',
          '--store-secondary': store.secondary_color ?? '#373f4e',
          '--store-navbar': store.navbar_color ?? '#17201e',
        } as React.CSSProperties
      }
    >
      <header className="mobile-shopbar">
        <div className="store-identity">
          {store.logo_url ? (
            <img src={store.logo_url} alt="" />
          ) : (
            <span>{store.business_name?.[0]}</span>
          )}
          <div>
            <h1>{store.business_name}</h1>
            <p>{t.title}</p>
          </div>
        </div>
        <div className="shopbar-actions">
          <button
            className="bag-icon-button"
            onClick={() => navigate(`/store/${slug}`)}
            aria-label={t.back}
          >
            <AppIcon name="home" size={20} />
          </button>
          <button
            className="language-toggle"
            onClick={() => setLanguage(language === 'en' ? 'ar' : 'en')}
            aria-label="Change language"
          >
            {language === 'en' ? 'ع' : 'EN'}
          </button>
        </div>
      </header>

      <main className="order-status-main">
        <section className="order-status-card order-status-header-card">
          <div className="order-status-meta">
            <span className="order-status-label">{t.orderNumber}</span>
            <strong className="order-status-number">{order.id}</strong>
          </div>
          <div className="order-status-current">
            <div className={`status-pulse ${order.status === 'CANCELLED' ? 'cancelled' : ''}`} />
            <div>
              <h2>{statusMessage}</h2>
              <span>{order.date}</span>
            </div>
          </div>
        </section>

        {order.status !== 'CANCELLED' && (
          <section className="order-status-card">
            <div className="order-timeline">
              {statusFlow.map((step, idx) => {
                const completed = idx <= currentIndex;
                const active = idx === currentIndex;
                return (
                  <div
                    key={step}
                    className={`order-timeline-step ${completed ? 'completed' : ''} ${active ? 'active' : ''}`}
                  >
                    <div className="order-timeline-dot">
                      {completed && <AppIcon name="check" size={14} />}
                    </div>
                    <span className="order-timeline-label">{(t.stages as any)[step]}</span>
                  </div>
                );
              })}
            </div>
          </section>
        )}

        <section className="order-status-card">
          <h3 className="order-status-section-title">{t.items}</h3>
          <ul className="order-status-items">
            {order.items.map((item, idx) => (
              <li key={idx}>
                <div>
                  <strong>{item.productName}</strong>
                  {item.variantName && <small>{item.variantName}</small>}
                </div>
                <span>x{item.quantity}</span>
                <strong>{money(item.unitPrice * item.quantity)}</strong>
              </li>
            ))}
          </ul>
          <div className="order-status-totals">
            <div>
              <span>{t.subtotal}</span>
              <span>{money(order.subtotal)}</span>
            </div>
            {order.fulfillment === 'DELIVERY' && (
              <div>
                <span>
                  {t.deliveryFee}
                  {order.deliveryProviderName ? ` · ${order.deliveryProviderName}` : ''}
                </span>
                {order.deliveryFeeStatus === 'PENDING' ? (
                  <span style={{ color: '#b45309', fontWeight: 700 }}>{t.feePending}</span>
                ) : (
                  <span>{money(order.deliveryFee ?? 0)}</span>
                )}
              </div>
            )}
            {Number(order.taxAmount) > 0 && (
              <div>
                <span>{t.tax}</span>
                <span>{money(order.taxAmount)}</span>
              </div>
            )}
            {Number(order.discount) > 0 && (
              <div className="discount">
                <span>{t.discount}</span>
                <span>-{money(order.discount)}</span>
              </div>
            )}
            <div className="grand-total">
              <span>{t.total}</span>
              <span>{money(order.total)}</span>
            </div>
          </div>
          {order.deliveryFeeStatus === 'PENDING' && (
            <p style={{ fontSize: '0.82rem', color: '#b45309', margin: '8px 0 0' }}>
              {t.feePendingHelp}
            </p>
          )}
        </section>

        {/* Pay after the merchant sets the fee (WhatsApp manual-quote loop). */}
        {order.fulfillment === 'DELIVERY' &&
          order.status !== 'CANCELLED' &&
          order.paymentStatus === 'UNPAID' &&
          order.deliveryFeeStatus === 'SET' &&
          store.fawran_enabled && (
            <section className="order-status-card">
              <h3 className="order-status-section-title">{t.payNow}</h3>
              <p
                style={{
                  fontSize: '0.85rem',
                  color: 'var(--text-muted, #64748b)',
                  margin: '0 0 10px',
                }}
              >
                {t.fawranPayHelp}
              </p>
              <div
                style={{ display: 'flex', flexDirection: 'column', gap: '8px', fontSize: '0.9rem' }}
              >
                {store.fawran_mobile && (
                  <div
                    style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      padding: '8px 12px',
                      background: '#f8fafc',
                      borderRadius: '8px',
                    }}
                  >
                    <span>{t.fawranAlias}</span>
                    <strong>{store.fawran_mobile}</strong>
                  </div>
                )}
                <div
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    padding: '8px 12px',
                    background: '#f8fafc',
                    borderRadius: '8px',
                  }}
                >
                  <span>{t.reference}</span>
                  <strong>{order.customerName}</strong>
                </div>
                <div
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    padding: '8px 12px',
                    background: '#f8fafc',
                    borderRadius: '8px',
                  }}
                >
                  <span>{t.total}</span>
                  <strong>{money(order.total)}</strong>
                </div>
              </div>
              <label
                className="file-picker"
                style={{ display: 'block', marginTop: '12px', fontWeight: 600 }}
              >
                {uploading ? '…' : t.uploadProof}
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  disabled={uploading}
                  onChange={(event) => {
                    const f = event.target.files?.[0];
                    if (f) handleProofUpload(f);
                    event.target.value = '';
                  }}
                />
              </label>
              {uploadError && (
                <p style={{ color: '#b91c1c', fontSize: '0.82rem', marginTop: '6px' }}>
                  {t.uploadFailed}
                </p>
              )}
            </section>
          )}
        {order.paymentStatus === 'PENDING_VERIFICATION' && (
          <section className="order-status-card" style={{ borderInlineStart: '4px solid #f59e0b' }}>
            <p style={{ margin: 0, fontSize: '0.88rem', fontWeight: 600 }}>{t.proofSent}</p>
          </section>
        )}

        <section className="order-status-card order-status-info">
          <h3 className="order-status-section-title">
            {t.fulfillment[order.fulfillment ?? 'PICKUP']}
          </h3>
          {order.fulfillment === 'DINE_IN' && order.tableNumber && (
            <p>
              <AppIcon name="table" size={16} /> {t.fulfillment.DINE_IN} — {t.table}{' '}
              {order.tableNumber}
            </p>
          )}
          {order.fulfillment === 'DELIVERY' && (
            <p>
              <AppIcon name="mapPin" size={16} /> {order.deliveryAddress}
              {order.deliveryCity ? `, ${order.deliveryCity}` : ''}
            </p>
          )}
          {order.fulfillment === 'PICKUP' && (
            <p>
              <AppIcon name="storefront" size={16} /> {t.fulfillment.PICKUP}
            </p>
          )}
          {order.notes && <p className="order-status-notes">{order.notes}</p>}
        </section>

        <section className="order-status-contact">
          <h3>{t.contact}</h3>
          <div className="order-status-contact-actions">
            {phoneUrl && (
              <a className="order-status-contact-button" href={phoneUrl}>
                <AppIcon name="phone" size={18} /> {t.call}
              </a>
            )}
            {whatsappUrl && (
              <a
                className="order-status-contact-button whatsapp"
                href={whatsappUrl}
                target="_blank"
                rel="noopener noreferrer"
              >
                <AppIcon name="whatsapp" size={18} /> {t.whatsapp}
              </a>
            )}
          </div>
        </section>

        <div className="order-status-actions">
          <AppButton variant="secondary" onClick={() => navigate(`/store/${slug}`)}>
            {t.back}
          </AppButton>
          <AppButton variant="primary" onClick={fetchOrder}>
            {t.refresh}
          </AppButton>
        </div>
      </main>
    </div>
  );
};
