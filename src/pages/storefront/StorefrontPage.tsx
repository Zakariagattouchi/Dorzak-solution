import React, { useState, useEffect, useMemo } from 'react';
import { useSettingsStore } from '../../stores/settingsStore';
import { useToastStore } from '../../stores/toastStore';
import { ToggleSwitch } from '../../components/forms/ToggleSwitch';
import { TextInput } from '../../components/forms/TextInput';
import { SelectInput } from '../../components/forms/SelectInput';
import { AppButton } from '../../components/buttons/AppButton';
import { useNavigate } from 'react-router-dom';
import { AppIcon } from '../../components/icons/AppIcon';
import { settingsApi, subscriptionApi } from '../../api/endpoints';

export const StorefrontPage: React.FC = () => {
  const { accountInfo, updateSettings, toggleOnlineStore, fetchSettings } = useSettingsStore();
  const { addToast } = useToastStore();
  const navigate = useNavigate();

  const [activeTab, setActiveTab] = useState<'STATUS' | 'BRANDING' | 'FULFILLMENT' | 'PAYMENTS'>(
    'STATUS',
  );

  // Form states
  const [storeSlug, setStoreSlug] = useState('');
  const [storePublicUrl, setStorePublicUrl] = useState('');
  const [storeBio, setStoreBio] = useState('');
  const [bannerUrl, setBannerUrl] = useState('');
  const [logoUrl, setLogoUrl] = useState('');
  const [bannerFile, setBannerFile] = useState<File | null>(null);
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [accentColor, setAccentColor] = useState('#BE000C');
  const [secondaryColor, setSecondaryColor] = useState('#373f4e');
  const [navbarColor, setNavbarColor] = useState('#17201e');
  const [productCardLayout, setProductCardLayout] = useState<'vertical' | 'horizontal'>('vertical');
  const [showStoreHeader, setShowStoreHeader] = useState(true);
  const [showStoreGradient, setShowStoreGradient] = useState(true);

  const [allowDelivery, setAllowDelivery] = useState(true);
  const [allowPickup, setAllowPickup] = useState(true);
  const [allowDineIn, setAllowDineIn] = useState(false);
  const [dineInTableCount, setDineInTableCount] = useState('0');
  const [deliveryFee, setDeliveryFee] = useState('5.0');
  const [freeDeliveryThreshold, setFreeDeliveryThreshold] = useState('50.0');
  const [minOrderAmount, setMinOrderAmount] = useState('10.0');

  const [whatsappOrdering, setWhatsappOrdering] = useState(true);
  const [whatsappDeliveryFallback, setWhatsappDeliveryFallback] = useState(false);
  const [fawranEnabled, setFawranEnabled] = useState(false);
  const [fawranAlias, setFawranAlias] = useState('');
  const [fawranMobile, setFawranMobile] = useState('');
  const [fawranIban, setFawranIban] = useState('');
  const [showOutOfStock, setShowOutOfStock] = useState(true);

  const [loading, setLoading] = useState(false);
  const [onFreePlan, setOnFreePlan] = useState(false);

  // Couriers collect from the store pin; without it delivery can't be offered.
  const storeHasLocation =
    (accountInfo as any)?.latitude != null && (accountInfo as any)?.longitude != null;

  // A branded storefront is a paid feature — surface that up front instead of
  // letting the save fail with a plan error.
  useEffect(() => {
    (subscriptionApi.get() as Promise<any>)
      .then((res) => setOnFreePlan(!!res?.data?.plan_is_default))
      .catch(() => {
        /* banner is best-effort */
      });
  }, []);

  // Sync state from store when accountInfo loaded
  useEffect(() => {
    if (accountInfo) {
      setStoreSlug(accountInfo.storeSlug || '');
      setStorePublicUrl(
        (accountInfo.publicUrl || '').trim() ||
          `${window.location.origin}/store/${accountInfo.storeSlug || ''}`,
      );
      setStoreBio(accountInfo.storeBio || '');
      setBannerUrl(accountInfo.bannerUrl || '');
      setLogoUrl(accountInfo.logoUrl || '');
      setAccentColor(accountInfo.storeAccentColor || '#BE000C');
      setSecondaryColor(accountInfo.storeSecondaryColor || '#373f4e');
      setNavbarColor(accountInfo.storeNavbarColor || '#17201e');
      setProductCardLayout(accountInfo.productCardLayout || 'vertical');
      setShowStoreHeader(accountInfo.showStoreHeader ?? true);
      setShowStoreGradient(accountInfo.showStoreGradient ?? true);
      setAllowDelivery(accountInfo.allowDelivery ?? true);
      setAllowPickup(accountInfo.allowPickup ?? true);
      setAllowDineIn((accountInfo as any).allowDineIn ?? false);
      setDineInTableCount(((accountInfo as any).dineInTableCount ?? 0).toString());
      setDeliveryFee((accountInfo.deliveryFee || 0).toString());
      setFreeDeliveryThreshold((accountInfo.freeDeliveryThreshold || 0).toString());
      setMinOrderAmount((accountInfo.minOrderAmount || 0).toString());
      setWhatsappOrdering(accountInfo.whatsappOrderingEnabled ?? true);
      setWhatsappDeliveryFallback((accountInfo as any).whatsappDeliveryFallback ?? false);
      setFawranEnabled(accountInfo.fawranEnabled ?? false);
      setFawranAlias(accountInfo.fawranAlias || '');
      setFawranMobile(accountInfo.fawranMobile || '');
      setFawranIban(accountInfo.fawranIban || '');
      setShowOutOfStock(accountInfo.showOutOfStockOnline ?? true);
    }
  }, [accountInfo]);

  const publicUrl = storePublicUrl || `${window.location.origin}/store/${storeSlug}`;
  const publicUrlPlaceholder = `${window.location.origin}/store/${storeSlug}`.trim();
  const storefrontUrl = (
    storePublicUrl && !storePublicUrl.includes('/store/')
      ? storePublicUrl.trim().replace(/\/+$/, '') + `/store/${storeSlug}`
      : publicUrl
  ).trim();
  const tableCount = Math.max(0, Math.min(500, parseInt(dineInTableCount || '0', 10) || 0));
  const tableLinks = useMemo(
    () =>
      Array.from({ length: tableCount }, (_, index) => {
        const table = index + 1;
        const url = `${storefrontUrl}?mode=dine_in&table=${table}`;
        return {
          table,
          url,
          qr: `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=12&data=${encodeURIComponent(url)}`,
        };
      }),
    [publicUrl, tableCount],
  );

  const handleBannerChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setBannerFile(file);
    if (file) {
      setBannerUrl(URL.createObjectURL(file));
    }
  };

  const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setLogoFile(file);
    if (file) {
      setLogoUrl(URL.createObjectURL(file));
    }
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      // Step 1: Upload files first — the backend sets banner_path / logo_path
      // via the upload endpoints, so we must NOT send bannerUrl / logoUrl
      // in the settings payload (they could contain blob: URLs or stale paths).
      if (bannerFile) {
        await settingsApi.uploadStorefront('banner', bannerFile);
      }
      if (logoFile) {
        await settingsApi.uploadStorefront('logo', logoFile);
      }

      // Step 2: Save all non-media storefront settings
      await updateSettings({
        storeSlug,
        publicUrl: storePublicUrl,
        storeBio,
        storeAccentColor: accentColor,
        storeSecondaryColor: secondaryColor,
        storeNavbarColor: navbarColor,
        allowDelivery,
        allowPickup,
        allowDineIn,
        dineInTableCount: tableCount,
        deliveryFee: parseFloat(deliveryFee) || 0,
        freeDeliveryThreshold: parseFloat(freeDeliveryThreshold) || 0,
        minOrderAmount: parseFloat(minOrderAmount) || 0,
        whatsappOrderingEnabled: whatsappOrdering,
        whatsappDeliveryFallback,
        fawranEnabled,
        fawranAlias,
        fawranMobile,
        fawranIban,
        showOutOfStockOnline: showOutOfStock,
        productCardLayout,
        showStoreHeader,
        showStoreGradient,
      });

      // Step 3: Refresh to get the newly generated media URLs from backend
      await fetchSettings();
      setBannerFile(null);
      setLogoFile(null);

      addToast('Online storefront customization saved!', 'success');
    } catch (err: any) {
      if (err?.code === 'PLAN_UPGRADE_REQUIRED') {
        addToast(
          'A branded storefront is a paid feature — start a free trial or upgrade to claim your store link',
          'info',
        );
        navigate('/billing');
      } else {
        addToast(err?.message ?? 'Failed to save storefront options', 'danger');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div
      style={{ maxWidth: '900px', display: 'flex', flexDirection: 'column', gap: 'var(--space-6)' }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h2 className="text-title-lg" style={{ margin: 0 }}>
            Online Storefront Customizer
          </h2>
          <span className="text-body-sm text-muted">
            Configure your public web catalog, online ordering wizard, banner, and WhatsApp
            integration
          </span>
        </div>
        <AppButton variant="secondary" onClick={() => navigate(`/catalog/preview`)}>
          <AppIcon name="eye" size={16} /> Live Catalog Preview
        </AppButton>
      </div>

      {onFreePlan && (
        <div
          className="card"
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: '12px',
            flexWrap: 'wrap',
            borderLeft: '4px solid var(--dorzak-primary)',
          }}
        >
          <AppIcon name="sparkles" size={20} color="var(--dorzak-primary)" />
          <div style={{ flex: 1, minWidth: '220px' }}>
            <div style={{ fontWeight: 700, fontSize: '0.9rem' }}>
              Your branded storefront is a paid feature
            </div>
            <span style={{ fontSize: '0.82rem', color: 'var(--text-muted)' }}>
              On the Free plan customers see your view-only menu link. Claim your own store link,
              online ordering and delivery with a free trial.
            </span>
          </div>
          <AppButton variant="primary" onClick={() => navigate('/billing')}>
            View plans
          </AppButton>
        </div>
      )}

      {/* Delivery needs a pickup point — couriers collect from your store pin. */}
      {allowDelivery && !storeHasLocation && (
        <div
          className="card"
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: '12px',
            flexWrap: 'wrap',
            borderLeft: '4px solid var(--dorzak-warning, #f59e0b)',
            backgroundColor: '#fef3c7',
          }}
        >
          <AppIcon name="mapPin" size={20} color="#92400e" />
          <div style={{ flex: 1, minWidth: '220px' }}>
            <div style={{ fontWeight: 700, fontSize: '0.9rem', color: '#92400e' }}>
              Delivery is off until you set your store location
            </div>
            <span style={{ fontSize: '0.82rem', color: '#92400e' }}>
              Couriers collect from your store pin — without it we can't price a delivery or tell a
              driver where to go.
            </span>
          </div>
          <AppButton variant="primary" onClick={() => navigate('/config')}>
            Set location
          </AppButton>
        </div>
      )}

      {/* Tabs */}
      <div
        style={{
          display: 'flex',
          gap: '4px',
          borderBottom: '1px solid var(--color-border)',
          overflowX: 'auto',
        }}
      >
        {(['STATUS', 'BRANDING', 'FULFILLMENT', 'PAYMENTS'] as const).map((tab) => (
          <button
            key={tab}
            type="button"
            onClick={() => setActiveTab(tab)}
            style={{
              padding: '10px 16px',
              fontSize: '13px',
              fontWeight: 700,
              borderBottom:
                activeTab === tab ? '2px solid var(--color-primary)' : '2px solid transparent',
              color: activeTab === tab ? 'var(--color-primary)' : 'var(--color-ink-muted)',
              textTransform: 'uppercase',
              letterSpacing: '0.05em',
              transition: 'all var(--transition-fast)',
            }}
          >
            {tab === 'STATUS' && 'Store Status & Link'}
            {tab === 'BRANDING' && 'Branding & Banner'}
            {tab === 'FULFILLMENT' && 'Delivery, Pickup & Dine-in'}
            {tab === 'PAYMENTS' && 'Checkout Methods'}
          </button>
        ))}
      </div>

      <form
        onSubmit={handleSave}
        className="card"
        style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}
      >
        {/* Tab 1: Status & Link */}
        {activeTab === 'STATUS' && (
          <>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                marginBottom: '12px',
              }}
            >
              <div>
                <h3 className="text-title-md" style={{ margin: 0 }}>
                  Online Catalog Activation
                </h3>
                <span className="text-body-sm text-muted">
                  Publish your online store for public customer orders
                </span>
              </div>
              <ToggleSwitch
                checked={accountInfo.onlineStoreEnabled}
                onChange={async () => {
                  await toggleOnlineStore();
                  addToast(
                    `Online store ${!accountInfo.onlineStoreEnabled ? 'enabled' : 'disabled'}`,
                    'info',
                  );
                }}
              />
            </div>

            {accountInfo.onlineStoreEnabled && (
              <div
                style={{
                  backgroundColor: 'var(--color-primary-soft)',
                  padding: '16px',
                  borderRadius: 'var(--radius-sm)',
                  border: '1px solid var(--color-primary-border)',
                }}
              >
                <span
                  className="text-label-sm"
                  style={{ color: 'var(--color-primary)', display: 'block', marginBottom: '4px' }}
                >
                  CUSTOM STORE WEB ADDRESS
                </span>
                <div style={{ display: 'flex', gap: '8px' }}>
                  <TextInput
                    value={storePublicUrl}
                    onChange={(e) => setStorePublicUrl(e.target.value.trim())}
                    placeholder={publicUrlPlaceholder}
                    style={{ flex: 1 }}
                  />
                  <AppButton
                    type="button"
                    variant="secondary"
                    onClick={() => {
                      window.open(storefrontUrl, '_blank', 'noopener');
                    }}
                  >
                    Open
                  </AppButton>
                  <AppButton
                    type="button"
                    variant="secondary"
                    onClick={() => {
                      navigator.clipboard.writeText(storefrontUrl);
                      addToast('Storefront URL copied!', 'success');
                    }}
                  >
                    Copy
                  </AppButton>
                </div>
              </div>
            )}

            <TextInput
              label="Store Link Slug"
              value={storeSlug}
              onChange={(e) =>
                setStoreSlug(e.target.value.toLowerCase().replace(/[^a-z0-9\-]/g, ''))
              }
              placeholder="e.g. my-store-name"
            />
          </>
        )}

        {/* Tab 2: Branding & Banner */}
        {activeTab === 'BRANDING' && (
          <>
            <h3 className="text-title-md" style={{ margin: 0 }}>
              Visual Branding & Catalog Header
            </h3>
            <TextInput
              label="Store Header Bio / Welcome Message"
              value={storeBio}
              onChange={(e) => setStoreBio(e.target.value)}
              placeholder="e.g. Welcome to our online catalog! Free delivery on orders over $50."
            />

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
              <label
                className="file-upload-card"
                style={{
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  border: '1px dashed var(--color-border-strong)',
                  padding: '16px',
                  borderRadius: 'var(--radius-sm)',
                  cursor: 'pointer',
                }}
              >
                {bannerUrl && (
                  <img
                    src={bannerUrl}
                    alt="Current banner"
                    style={{
                      maxHeight: '100px',
                      maxWidth: '100%',
                      marginBottom: '8px',
                      objectFit: 'cover',
                    }}
                  />
                )}
                <strong>Cover Banner</strong>
                <span className="text-label-sm text-muted">JPG, PNG or WebP</span>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  onChange={handleBannerChange}
                  style={{ marginTop: '8px' }}
                />
              </label>

              <label
                className="file-upload-card"
                style={{
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  border: '1px dashed var(--color-border-strong)',
                  padding: '16px',
                  borderRadius: 'var(--radius-sm)',
                  cursor: 'pointer',
                }}
              >
                {logoUrl && (
                  <img
                    src={logoUrl}
                    alt="Current logo"
                    style={{
                      maxHeight: '100px',
                      maxWidth: '100%',
                      marginBottom: '8px',
                      objectFit: 'contain',
                    }}
                  />
                )}
                <strong>Store Logo</strong>
                <span className="text-label-sm text-muted">JPG, PNG or WebP</span>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  onChange={handleLogoChange}
                  style={{ marginTop: '8px' }}
                />
              </label>
            </div>

            <div className="form-group">
              <label className="form-label">Primary Color</label>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <input
                  type="color"
                  value={accentColor}
                  onChange={(e) => setAccentColor(e.target.value)}
                  style={{
                    width: '60px',
                    height: '36px',
                    padding: '0',
                    borderRadius: 'var(--radius-xs)',
                    border: '1px solid var(--color-border-strong)',
                    cursor: 'pointer',
                  }}
                />
                <span className="text-body-sm">{accentColor}</span>
              </div>
            </div>

            <div className="form-group">
              <label className="form-label">Secondary Color</label>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <input
                  type="color"
                  value={secondaryColor}
                  onChange={(e) => setSecondaryColor(e.target.value)}
                  style={{
                    width: '60px',
                    height: '36px',
                    padding: '0',
                    borderRadius: 'var(--radius-xs)',
                    border: '1px solid var(--color-border-strong)',
                    cursor: 'pointer',
                  }}
                />
                <span className="text-body-sm">{secondaryColor}</span>
              </div>
            </div>

            <div className="form-group">
              <label className="form-label">Header & Footer Navbar Color</label>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <input
                  type="color"
                  value={navbarColor}
                  onChange={(e) => setNavbarColor(e.target.value)}
                  style={{
                    width: '60px',
                    height: '36px',
                    padding: '0',
                    borderRadius: 'var(--radius-xs)',
                    border: '1px solid var(--color-border-strong)',
                    cursor: 'pointer',
                  }}
                />
                <span className="text-body-sm">{navbarColor}</span>
              </div>
            </div>

            <div style={{ borderTop: '1px solid var(--color-border)', margin: '8px 0' }} />

            <SelectInput
              label="Product Card Layout"
              value={productCardLayout}
              onChange={(e) => setProductCardLayout(e.target.value as 'vertical' | 'horizontal')}
              options={[
                { value: 'vertical', label: 'Vertical cards (image on top)' },
                { value: 'horizontal', label: 'Horizontal cards (image on the side)' },
              ]}
            />

            <ToggleSwitch
              checked={showStoreHeader}
              onChange={setShowStoreHeader}
              label="Show Store Header Welcome Message"
              description="Display the cover banner area with the store bio / welcome text"
            />

            <ToggleSwitch
              checked={showStoreGradient}
              onChange={setShowStoreGradient}
              label="Show Hero Gradient Background"
              description="Apply the accent gradient behind the header text when no banner image is uploaded"
            />
          </>
        )}

        {/* Tab 3: Fulfillment */}
        {activeTab === 'FULFILLMENT' && (
          <>
            <h3 className="text-title-md" style={{ margin: 0 }}>
              Fulfillment & Shipping Rules
            </h3>
            <ToggleSwitch
              checked={allowDelivery}
              onChange={setAllowDelivery}
              label="Enable Home Delivery Service"
            />
            <ToggleSwitch
              checked={whatsappDeliveryFallback}
              onChange={setWhatsappDeliveryFallback}
              label="WhatsApp delivery quotes"
              description="When no courier covers an order, accept it anyway — you confirm the delivery fee over WhatsApp and the customer pays once it's set."
            />
            <ToggleSwitch
              checked={allowPickup}
              onChange={setAllowPickup}
              label="Enable In-Store Pickup"
            />
            <ToggleSwitch
              checked={allowDineIn}
              onChange={setAllowDineIn}
              label="Enable Dine-in Table Ordering"
              description="Customers scan a table QR code, open the storefront, and place an order linked to that table."
            />

            {allowDineIn && (
              <div className="dine-in-settings-panel">
                <div className="dine-in-settings-copy">
                  <div className="dine-in-icon">
                    <AppIcon name="scanQr" size={22} />
                  </div>
                  <div>
                    <h4>Dine-in table QR codes</h4>
                    <p>
                      Enter how many tables you have. Dorzak will generate one QR code per table and
                      attach the table number to every order.
                    </p>
                  </div>
                </div>
                <TextInput
                  label="Number of tables"
                  type="number"
                  min="0"
                  max="500"
                  step="1"
                  value={dineInTableCount}
                  onChange={(e) => setDineInTableCount(e.target.value)}
                />
                {tableLinks.length > 0 && (
                  <>
                    <div className="dine-in-qr-toolbar">
                      <span>{tableLinks.length} table QR codes ready</span>
                      <AppButton type="button" variant="secondary" onClick={() => window.print()}>
                        <AppIcon name="qr" size={16} /> Print QR Codes
                      </AppButton>
                    </div>
                    <div className="dine-in-qr-grid">
                      {tableLinks.map((link) => (
                        <article className="dine-in-qr-card" key={link.table}>
                          <span className="dine-in-table-label">
                            <AppIcon name="table" size={16} /> Table {link.table}
                          </span>
                          <img
                            src={link.qr}
                            alt={`QR code for table ${link.table}`}
                            loading="lazy"
                          />
                          <small>{link.url}</small>
                          <div>
                            <AppButton
                              type="button"
                              variant="secondary"
                              onClick={() => {
                                navigator.clipboard.writeText(link.url);
                                addToast(`Table ${link.table} link copied`, 'success');
                              }}
                            >
                              Copy Link
                            </AppButton>
                            <a
                              className="btn btn-secondary"
                              href={link.qr}
                              target="_blank"
                              rel="noopener noreferrer"
                            >
                              Open QR
                            </a>
                          </div>
                        </article>
                      ))}
                    </div>
                  </>
                )}
              </div>
            )}

            <div style={{ borderTop: '1px solid var(--color-border)', margin: '8px 0' }} />
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px' }}>
              <TextInput
                label="Standard Delivery Fee ($)"
                type="number"
                step="0.5"
                value={deliveryFee}
                onChange={(e) => setDeliveryFee(e.target.value)}
              />
              <TextInput
                label="Free Delivery Threshold ($)"
                type="number"
                step="1"
                value={freeDeliveryThreshold}
                onChange={(e) => setFreeDeliveryThreshold(e.target.value)}
              />
              <TextInput
                label="Minimum Order Value ($)"
                type="number"
                step="1"
                value={minOrderAmount}
                onChange={(e) => setMinOrderAmount(e.target.value)}
              />
            </div>
          </>
        )}

        {/* Tab 4: Checkout */}
        {activeTab === 'PAYMENTS' && (
          <>
            <h3 className="text-title-md" style={{ margin: 0 }}>
              Direct WhatsApp Ordering Integration
            </h3>
            <ToggleSwitch
              checked={whatsappOrdering}
              onChange={setWhatsappOrdering}
              label="Enable Instant WhatsApp Checkout"
              description="Automatically generate a pre-formatted order summary message sent directly to your WhatsApp Business number when a customer submits an online order"
            />
            <div style={{ borderTop: '1px solid var(--color-border)', margin: '8px 0' }} />
            <ToggleSwitch
              checked={fawranEnabled}
              onChange={setFawranEnabled}
              label="Enable Fawran Transfer"
              description="Customers submit a transfer reference and optional payment receipt for verification."
            />
            {fawranEnabled && (
              <div
                className="form-grid-2"
                style={{
                  display: 'grid',
                  gridTemplateColumns: '1fr 1fr',
                  gap: '16px',
                  marginTop: '8px',
                }}
              >
                <TextInput
                  label="Fawran Alias"
                  value={fawranAlias}
                  onChange={(e) => setFawranAlias(e.target.value)}
                />
                <TextInput
                  label="Fawran Mobile"
                  value={fawranMobile}
                  onChange={(e) => setFawranMobile(e.target.value)}
                />
                <TextInput
                  label="Fawran IBAN"
                  value={fawranIban}
                  onChange={(e) => setFawranIban(e.target.value)}
                  style={{ gridColumn: 'span 2' }}
                />
              </div>
            )}

            <div style={{ borderTop: '1px solid var(--color-border)', margin: '8px 0' }} />
            <ToggleSwitch
              checked={showOutOfStock}
              onChange={setShowOutOfStock}
              label="Display Out of Stock Items with Badge"
              description="Show items in catalog with 'Out of Stock' badge instead of hiding them completely"
            />
          </>
        )}

        <div
          style={{
            display: 'flex',
            justifyContent: 'flex-end',
            borderTop: '1px solid var(--color-border)',
            paddingTop: '16px',
          }}
        >
          <AppButton variant="primary" type="submit" loading={loading}>
            Save Storefront Options
          </AppButton>
        </div>
      </form>
    </div>
  );
};
