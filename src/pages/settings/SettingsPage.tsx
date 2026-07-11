import React, { useState, useEffect } from 'react';
import { useSettingsStore } from '../../stores/settingsStore';
import { useAuthStore } from '../../stores/authStore';
import { useToastStore } from '../../stores/toastStore';
import { TextInput } from '../../components/forms/TextInput';
import { SelectInput } from '../../components/forms/SelectInput';
import { ToggleSwitch } from '../../components/forms/ToggleSwitch';
import { CheckboxInput } from '../../components/forms/CheckboxInput';
import { LocationPicker } from '../../components/forms/LocationPicker';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon, IconName } from '../../components/icons/AppIcon';
import { settingsApi, subscriptionApi, loyaltyApi } from '../../api/endpoints';

type Tab = 'GENERAL' | 'BUSINESS' | 'CURRENCY' | 'TAXES' | 'RECEIPTS' | 'PAYMENTS' | 'INTEGRATIONS' | 'LOYALTY' | 'STAFF' | 'SUBSCRIPTION';

const TAB_CONFIG: { key: Tab; label: string; icon: IconName }[] = [
  { key: 'GENERAL', label: 'General', icon: 'storefront' },
  { key: 'BUSINESS', label: 'Business Info', icon: 'building' },
  { key: 'CURRENCY', label: 'Currency', icon: 'refresh' },
  { key: 'TAXES', label: 'Taxes', icon: 'reports' },
  { key: 'RECEIPTS', label: 'Receipts', icon: 'receipt' },
  { key: 'PAYMENTS', label: 'Payments', icon: 'card' },
  { key: 'INTEGRATIONS', label: 'Integrations', icon: 'link' },
  { key: 'LOYALTY', label: 'Loyalty', icon: 'star' },
  { key: 'STAFF', label: 'Users & Staff', icon: 'customers' },
  { key: 'SUBSCRIPTION', label: 'Subscription', icon: 'star' },
];

export const SettingsPage: React.FC = () => {
  const { accountInfo, updateSettings, setLanguage } = useSettingsStore();
  const { addToast } = useToastStore();
  const can = useAuthStore((s) => s.can);
  const isAdmin = can('settings.manage');

  const [activeTab, setActiveTab] = useState<Tab>('GENERAL');
  const [loading, setLoading] = useState(false);

  // GENERAL
  const [businessName, setBusinessName] = useState(accountInfo.businessName);
  const [tagline, setTagline] = useState(accountInfo.tagline || '');
  const [phone, setPhone] = useState(accountInfo.phone);
  const [whatsapp, setWhatsapp] = useState(accountInfo.whatsapp || '');
  const [language, setLanguageValue] = useState<'en' | 'ar'>(accountInfo.language || 'en');

  // BUSINESS
  const [ownerName, setOwnerName] = useState(accountInfo.ownerName);
  const [email, setEmail] = useState(accountInfo.email);
  const [address, setAddress] = useState(accountInfo.address || '');
  const [city, setCity] = useState(accountInfo.city || '');
  const [stateField, setStateField] = useState(accountInfo.state || '');
  const [zipCode, setZipCode] = useState(accountInfo.zipCode || '');
  const [country, setCountry] = useState(accountInfo.country || 'United States');
  const [latitude, setLatitude] = useState<number | null>(accountInfo.latitude ?? null);
  const [longitude, setLongitude] = useState<number | null>(accountInfo.longitude ?? null);

  // CURRENCY
  const [currency, setCurrency] = useState(accountInfo.currency);
  const [symbolPlacement, setSymbolPlacement] = useState(accountInfo.symbolPlacement);

  // TAXES
  const [taxRate, setTaxRate] = useState(accountInfo.taxRate.toString());
  const [taxId, setTaxId] = useState(accountInfo.taxId || '');
  const [taxIncluded, setTaxIncluded] = useState(accountInfo.taxIncludedInPrice || false);
  const [chargeSalesTax, setChargeSalesTax] = useState(true);

  // LOYALTY (premium)
  const [loyaltyEnabled, setLoyaltyEnabled] = useState(false);
  const [loyaltyEarn, setLoyaltyEarn] = useState('1');
  const [loyaltyRedeemPoints, setLoyaltyRedeemPoints] = useState('100');
  const [loyaltyRedeemValue, setLoyaltyRedeemValue] = useState('5');
  const [loyaltyLoading, setLoyaltyLoading] = useState(false);

  useEffect(() => {
    loyaltyApi.get().then((res: any) => {
      const l = res?.loyalty;
      if (l) {
        setLoyaltyEnabled(!!l.enabled);
        setLoyaltyEarn(String(l.earn_points_per_currency));
        setLoyaltyRedeemPoints(String(l.redeem_points));
        setLoyaltyRedeemValue(String(l.redeem_value));
      }
    }).catch(() => {});
  }, []);

  const handleSaveLoyalty = async () => {
    setLoyaltyLoading(true);
    try {
      await loyaltyApi.update({
        enabled: loyaltyEnabled,
        earn_points_per_currency: parseInt(loyaltyEarn, 10) || 0,
        redeem_points: parseInt(loyaltyRedeemPoints, 10) || 1,
        redeem_value: parseFloat(loyaltyRedeemValue) || 0,
      });
      addToast('Loyalty program saved.', 'success');
    } catch (err: any) {
      addToast(err?.code === 'PLAN_UPGRADE_REQUIRED' ? 'Upgrade your plan to use loyalty.' : 'Could not save loyalty settings.', 'danger');
    } finally {
      setLoyaltyLoading(false);
    }
  };

  // RECEIPTS
  const [receiptHeader, setReceiptHeader] = useState(accountInfo.receiptHeader || '');
  const [receiptFooter, setReceiptFooter] = useState(accountInfo.receiptFooter || '');
  const [receiptShowLogo, setReceiptShowLogo] = useState(accountInfo.receiptShowLogo ?? true);
  const [receiptShowAddress, setReceiptShowAddress] = useState(accountInfo.receiptShowAddress ?? true);
  const [receiptShowTax, setReceiptShowTax] = useState(accountInfo.receiptShowTax ?? true);
  const [autoPrint, setAutoPrint] = useState(accountInfo.autoPrintReceipt ?? false);

  // PAYMENTS
  const [payLoading, setPayLoading] = useState(false);
  const [acceptCash, setAcceptCash] = useState(true);
  const [acceptCard, setAcceptCard] = useState(true);
  const [acceptTransfer, setAcceptTransfer] = useState(true);
  const [acceptWhatsapp, setAcceptWhatsapp] = useState(true);
  const [bankName, setBankName] = useState('');
  const [bankAccountName, setBankAccountName] = useState('');
  const [bankAccountNumber, setBankAccountNumber] = useState('');
  const [bankIban, setBankIban] = useState('');
  const [whatsappPhone, setWhatsappPhone] = useState('');
  // Online payment methods (synced to storefront settings)
  const [whatsappOrderingEnabled, setWhatsappOrderingEnabled] = useState(false);
  const [fawranEnabled, setFawranEnabled] = useState(false);
  const [fawranAlias, setFawranAlias] = useState('');
  const [fawranIban, setFawranIban] = useState('');
  const [fawranMobile, setFawranMobile] = useState('');

  // SUBSCRIPTION
  const [subData, setSubData] = useState<any>(null);
  const [subLoading, setSubLoading] = useState(false);

  // INTEGRATIONS
  const [facebookPixelId, setFacebookPixelId] = useState('');
  const [googleAnalyticsId, setGoogleAnalyticsId] = useState('');
  const [facebookLinked, setFacebookLinked] = useState(false);

  const currencySymbol = currency === 'QAR'
    ? 'QAR '
    : ({ USD: '$', EUR: '€', GBP: '£', CAD: 'CA$', BRL: 'R$', MXN: '$', COP: '$', ARS: '$', AUD: 'A$' } as Record<string, string>)[currency] || currency;

  // The settings envelope arrives asynchronously (and again after every save).
  // Re-seed the form from it, or a page loaded before the fetch resolves keeps
  // showing the empty placeholder state — and could save it over real data.
  useEffect(() => {
    if (!accountInfo) return;
    setBusinessName(accountInfo.businessName ?? '');
    setTagline(accountInfo.tagline ?? '');
    setPhone(accountInfo.phone ?? '');
    setWhatsapp(accountInfo.whatsapp ?? '');
    setOwnerName(accountInfo.ownerName ?? '');
    setEmail(accountInfo.email ?? '');
    setAddress(accountInfo.address ?? '');
    setCity(accountInfo.city ?? '');
    setStateField(accountInfo.state ?? '');
    setZipCode(accountInfo.zipCode ?? '');
    setCountry(accountInfo.country || 'United States');
    setLatitude(accountInfo.latitude ?? null);
    setLongitude(accountInfo.longitude ?? null);
    setCurrency(accountInfo.currency);
    setSymbolPlacement(accountInfo.symbolPlacement);
    setTaxRate((accountInfo.taxRate ?? 0).toString());
    setTaxId(accountInfo.taxId ?? '');
    setTaxIncluded(accountInfo.taxIncludedInPrice ?? false);
    setChargeSalesTax((accountInfo as any).chargeSalesTax ?? true);
    setReceiptHeader(accountInfo.receiptHeader ?? '');
    setReceiptFooter(accountInfo.receiptFooter ?? '');
    setReceiptShowLogo(accountInfo.receiptShowLogo ?? true);
    setReceiptShowAddress(accountInfo.receiptShowAddress ?? true);
    setReceiptShowTax(accountInfo.receiptShowTax ?? true);
    setAutoPrint(accountInfo.autoPrintReceipt ?? false);
  }, [accountInfo]);

  // Load payment + subscription data when their tabs become active
  useEffect(() => {
    if (activeTab !== 'PAYMENTS' && activeTab !== 'SUBSCRIPTION') return;
    const load = async () => {
      try {
        const res: any = await settingsApi.get();
        const p = res.data?.payments ?? {};
        setAcceptCash(p.cash ?? true);
        setAcceptCard(p.card ?? true);
        setAcceptTransfer(p.transfer ?? true);
        setAcceptWhatsapp(p.whatsapp ?? true);
        setBankName(p.bank_name ?? '');
        setBankAccountName(p.bank_account_name ?? '');
        setBankAccountNumber(p.bank_account_number ?? '');
        setBankIban(p.bank_iban ?? '');
        setWhatsappPhone(p.whatsapp_phone ?? '');
        const sf = res.data?.storefront ?? {};
        setWhatsappOrderingEnabled(sf.whatsapp_ordering_enabled ?? false);
        setFawranEnabled(sf.fawran_enabled ?? false);
        setFawranAlias(sf.fawran_alias ?? '');
        setFawranIban(sf.fawran_iban ?? '');
        setFawranMobile(sf.fawran_mobile ?? '');
      } catch { /* ignore */ }
    };
    load();
  }, [activeTab]);

  // Fetch subscription data when SUBSCRIPTION tab becomes active
  useEffect(() => {
    if (activeTab !== 'SUBSCRIPTION') return;
    setSubLoading(true);
    subscriptionApi.get()
      .then((res: any) => setSubData(res.data))
      .catch(() => setSubData(null))
      .finally(() => setSubLoading(false));
  }, [activeTab]);

  const handleLanguageChange = (value: 'en' | 'ar') => {
    setLanguageValue(value);
    setLanguage(value);
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await updateSettings({
        businessName, tagline, phone, whatsapp,
        ownerName, email, address, city,
        state: stateField, zipCode, country,
        latitude, longitude,
        language, currency, currencySymbol: currencySymbol.trim(), symbolPlacement,
        taxRate: parseFloat(taxRate) || 0,
        taxId, taxIncludedInPrice: taxIncluded,
        receiptHeader, receiptFooter,
        receiptShowLogo, receiptShowAddress,
        receiptShowTax, autoPrintReceipt: autoPrint,
      });
      addToast('Settings saved successfully!', 'success');
    } catch {
      addToast('Failed to save settings', 'danger');
    } finally {
      setLoading(false);
    }
  };

  const handleSavePayments = async () => {
    setPayLoading(true);
    try {
      await settingsApi.update('payments', {
        cash: acceptCash,
        card: acceptCard,
        transfer: acceptTransfer,
        whatsapp: acceptWhatsapp || whatsappOrderingEnabled,
        bank_name: bankName,
        bank_account_name: bankAccountName,
        bank_account_number: bankAccountNumber,
        bank_iban: bankIban,
        whatsapp_phone: whatsappPhone || accountInfo.whatsapp,
      });
      await settingsApi.update('storefront', {
        whatsapp_ordering_enabled: whatsappOrderingEnabled,
        fawran_enabled: fawranEnabled,
        fawran_alias: fawranAlias,
        fawran_iban: fawranIban,
        fawran_mobile: fawranMobile,
      });
      addToast('Payment settings saved!', 'success');
    } catch {
      addToast('Failed to save payment settings', 'danger');
    } finally {
      setPayLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', gap: '24px', maxWidth: '1100px' }}>
      {/* LEFT: Vertical Tab Sidebar */}
      <div style={{
        width: '200px',
        flexShrink: 0,
        display: 'flex',
        flexDirection: 'column',
        gap: '2px',
      }}>
        <div style={{ fontSize: '0.7rem', fontWeight: 700, color: 'var(--text-muted)', padding: '8px 12px', letterSpacing: '0.08em', textTransform: 'uppercase' }}>
          Configuration
        </div>
        {TAB_CONFIG.filter(tab => tab.key !== 'PAYMENTS' || isAdmin).map(tab => (
          <button
            key={tab.key}
            type="button"
            onClick={() => setActiveTab(tab.key)}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '10px',
              padding: '10px 14px',
              borderRadius: '8px',
              backgroundColor: activeTab === tab.key ? 'var(--dorzak-primary-light)' : 'transparent',
              color: activeTab === tab.key ? 'var(--dorzak-primary)' : 'var(--text-main)',
              fontWeight: activeTab === tab.key ? 600 : 400,
              fontSize: '0.9rem',
              textAlign: 'left',
              width: '100%',
              border: 'none',
              cursor: 'pointer',
              transition: 'background 0.15s',
            }}
          >
            <AppIcon name={tab.icon} size={17} />
            {tab.label}
          </button>
        ))}
      </div>

      {/* RIGHT: Content Panel */}
      <div style={{ flex: 1, minWidth: 0 }}>
        <form onSubmit={handleSave} style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>

          {/* GENERAL */}
          {activeTab === 'GENERAL' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>General Store Settings</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Your store name, tagline, and contact numbers</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <div>
                <h4 style={{ margin: '0 0 12px 0', fontSize: '0.95rem' }}>Language & Region</h4>
                <SelectInput
                  label="Interface Language"
                  value={language}
                  onChange={e => handleLanguageChange(e.target.value as 'en' | 'ar')}
                  options={[
                    { value: 'en', label: 'English' },
                    { value: 'ar', label: 'Arabic' },
                  ]}
                />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                <TextInput label="Business / Store Name *" value={businessName} onChange={e => setBusinessName(e.target.value)} placeholder="e.g. Dorzak Merchant" />
                <TextInput label="Tagline / Slogan" value={tagline} onChange={e => setTagline(e.target.value)} placeholder="e.g. Quality products, fast delivery" />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                <TextInput label="Phone Number" value={phone} onChange={e => setPhone(e.target.value)} placeholder="+1 (555) 000-0000" />
                <TextInput label="WhatsApp Business Number" value={whatsapp} onChange={e => setWhatsapp(e.target.value)} placeholder="+1 (555) 000-0000" />
              </div>

              <div style={{
                backgroundColor: 'var(--dorzak-primary-light)',
                border: '1px solid var(--dorzak-primary)',
                borderRadius: '8px',
                padding: '14px 16px',
                display: 'flex',
                alignItems: 'center',
                gap: '12px',
              }}>
                <AppIcon name="lightbulb" size={20} color="var(--color-primary)" />
                <div>
                  <strong style={{ fontSize: '0.85rem', color: 'var(--dorzak-primary)' }}>WhatsApp tip</strong>
                  <p style={{ margin: '2px 0 0 0', fontSize: '0.8rem', color: 'var(--text-muted)' }}>Your WhatsApp number is used for direct customer order notifications from the online catalog.</p>
                </div>
              </div>
            </div>
          )}

          {/* BUSINESS INFO */}
          {activeTab === 'BUSINESS' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Business Information</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Legal business details, owner name, and registered address</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                <TextInput label="Account Owner Name" value={ownerName} onChange={e => setOwnerName(e.target.value)} />
                <TextInput label="Contact Email Address" type="email" value={email} onChange={e => setEmail(e.target.value)} />
              </div>
              <h4 style={{ margin: '4px 0 0 0', fontSize: '0.95rem' }}>Physical Business Address</h4>
              <TextInput label="Street Address" value={address} onChange={e => setAddress(e.target.value)} placeholder="e.g. 123 Main St, Suite 4" />
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px' }}>
                <TextInput label="City" value={city} onChange={e => setCity(e.target.value)} />
                <TextInput label="State / Province" value={stateField} onChange={e => setStateField(e.target.value)} />
                <TextInput label="ZIP / Postal Code" value={zipCode} onChange={e => setZipCode(e.target.value)} />
              </div>
              <SelectInput
                label="Country"
                value={country}
                onChange={e => setCountry(e.target.value)}
                options={[
                  { value: 'United States', label: 'United States' },
                  { value: 'United Kingdom', label: 'United Kingdom' },
                  { value: 'Canada', label: 'Canada' },
                  { value: 'Brazil', label: 'Brazil' },
                  { value: 'Mexico', label: 'Mexico' },
                  { value: 'Panama', label: 'Panama' },
                  { value: 'Colombia', label: 'Colombia' },
                  { value: 'Argentina', label: 'Argentina' },
                  { value: 'Australia', label: 'Australia' },
                  { value: 'Germany', label: 'Germany' },
                  { value: 'France', label: 'France' },
                  { value: 'Qatar', label: 'Qatar' },
                ]}
              />

              <h4 style={{ margin: '4px 0 0 0', fontSize: '0.95rem' }}>Store Location on the Map</h4>
              <p style={{ margin: 0, fontSize: '0.82rem', color: 'var(--text-muted)' }}>
                Required to offer delivery — this is where your deliveries start from and how delivery fees are calculated.
              </p>
              <LocationPicker
                latitude={latitude ?? undefined}
                longitude={longitude ?? undefined}
                onChange={(lat, lng) => { setLatitude(lat); setLongitude(lng); }}
                onAddressResolved={(resolvedAddress) => { if (!address) setAddress(resolvedAddress); }}
              />
              {latitude != null && longitude != null && (
                <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>
                  Pinned at {Number(latitude).toFixed(5)}, {Number(longitude).toFixed(5)}
                </span>
              )}
            </div>
          )}

          {/* CURRENCY */}
          {activeTab === 'CURRENCY' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Currency & Formatting</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Choose your store currency and how prices are displayed</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                <SelectInput
                  label="Store Currency"
                  value={currency}
                  onChange={e => {
                    setCurrency(e.target.value);
                    if (e.target.value === 'QAR') setSymbolPlacement('BEFORE');
                  }}
                  options={[
                    { value: 'QAR', label: 'QAR — Qatari Riyal (ر.ق)' },
                    { value: 'USD', label: 'USD — US Dollar ($)' },
                    { value: 'EUR', label: 'EUR — Euro (€)' },
                    { value: 'GBP', label: 'GBP — British Pound (£)' },
                    { value: 'CAD', label: 'CAD — Canadian Dollar (CA$)' },
                    { value: 'BRL', label: 'BRL — Brazilian Real (R$)' },
                    { value: 'MXN', label: 'MXN — Mexican Peso ($)' },
                    { value: 'COP', label: 'COP — Colombian Peso ($)' },
                    { value: 'ARS', label: 'ARS — Argentine Peso ($)' },
                    { value: 'AUD', label: 'AUD — Australian Dollar (A$)' },
                  ]}
                />
                <SelectInput
                  label="Currency Symbol Position"
                  value={symbolPlacement}
                  onChange={e => setSymbolPlacement(e.target.value as 'BEFORE' | 'AFTER')}
                  options={[
                    { value: 'BEFORE', label: 'Before amount — $100.00' },
                    { value: 'AFTER', label: 'After amount — 100.00 $' },
                  ]}
                />
              </div>
              <div style={{ backgroundColor: 'var(--color-bg)', borderRadius: '8px', padding: '16px' }}>
                <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)', fontWeight: 600 }}>PRICE PREVIEW</span>
                <div style={{ fontSize: '1.6rem', fontWeight: 700, color: 'var(--dorzak-primary)', marginTop: '8px' }}>
                  {symbolPlacement === 'BEFORE' ? `${currencySymbol}49.99` : `49.99 ${currencySymbol}`}
                </div>
              </div>
            </div>
          )}

          {/* TAXES */}
          {activeTab === 'TAXES' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Sales Tax Configuration</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Configure sales tax rates applied to POS and online transactions</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <ToggleSwitch checked={chargeSalesTax} onChange={setChargeSalesTax} label="Charge Sales Taxes" description="Apply tax rate to all taxable products in sales and receipts" />
              {chargeSalesTax && (
                <>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <TextInput label="Default Sales Tax Rate (%)" type="number" step="0.01" value={taxRate} onChange={e => setTaxRate(e.target.value)} placeholder="e.g. 8.5" />
                    <TextInput label="Tax Registration ID / VAT Number" value={taxId} onChange={e => setTaxId(e.target.value)} placeholder="e.g. US-991827364" />
                  </div>
                  <ToggleSwitch
                    checked={taxIncluded}
                    onChange={setTaxIncluded}
                    label="Tax already included in product prices"
                    description="Enable this if your product prices already include the tax amount (prices shown to customers won't change)"
                  />
                </>
              )}
            </div>
          )}

          {/* RECEIPTS */}
          {activeTab === 'RECEIPTS' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Receipt Customization</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Personalize the header and footer text shown on printed and emailed receipts</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />

              <TextInput
                label="Receipt Header Message"
                value={receiptHeader}
                onChange={e => setReceiptHeader(e.target.value)}
                placeholder={accountInfo.businessName}
              />
              <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)', marginTop: '-12px' }}>
                Appears at the top of every receipt, above the order items
              </div>

              <TextInput
                label="Receipt Notes / Footer Text"
                value={receiptFooter}
                onChange={e => setReceiptFooter(e.target.value)}
                placeholder="e.g. Returns accepted within 30 days"
              />

              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <h4 style={{ margin: 0, fontSize: '0.95rem' }}>Print Options</h4>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                <ToggleSwitch checked={receiptShowLogo} onChange={setReceiptShowLogo} label="Print store logo on receipt" description="Show your business logo at the top of printed receipts" />
                <ToggleSwitch checked={receiptShowAddress} onChange={setReceiptShowAddress} label="Print business address on receipt" description="Include your store address in the receipt footer" />
                <ToggleSwitch checked={receiptShowTax} onChange={setReceiptShowTax} label="Show tax breakdown on receipt" description="Display the applied tax amount separately on receipts" />
                <ToggleSwitch checked={autoPrint} onChange={setAutoPrint} label="Auto-print receipt after sale" description="Automatically open the print dialog after completing a sale at the POS" />
              </div>

              <div style={{ backgroundColor: 'var(--color-bg)', borderRadius: '10px', padding: '20px', border: '1px dashed var(--color-border)' }}>
                <div style={{ fontWeight: 700, marginBottom: '12px', fontSize: '0.95rem' }}>Receipt Preview</div>
                <div style={{ fontFamily: 'monospace', fontSize: '0.85rem', lineHeight: 1.8, color: 'var(--text-main)' }}>
                  <div style={{ textAlign: 'center', fontWeight: 700, marginBottom: '6px' }}>{receiptHeader || accountInfo.businessName}</div>
                  <div style={{ textAlign: 'center', color: 'var(--text-muted)', fontSize: '0.75rem', marginBottom: '12px' }}>{accountInfo.phone}</div>
                  <div>──────────────────</div>
                  <div>Item 1 × 2 ........... $99.98</div>
                  <div>Item 2 × 1 ........... $29.00</div>
                  <div>──────────────────</div>
                  <div>Subtotal .............. $128.98</div>
                  {receiptShowTax && <div>Tax (8.5%) ............. $10.96</div>}
                  <div style={{ fontWeight: 700 }}>TOTAL ................. $139.94</div>
                  <div>──────────────────</div>
                  {receiptShowAddress && <div style={{ color: 'var(--text-muted)', fontSize: '0.75rem', textAlign: 'center' }}>{accountInfo.address}</div>}
                  {receiptFooter && <div style={{ color: 'var(--text-muted)', fontSize: '0.75rem', textAlign: 'center', marginTop: '6px' }}>{receiptFooter}</div>}
                </div>
              </div>
            </div>
          )}

          {/* PAYMENTS */}
          {activeTab === 'PAYMENTS' && !isAdmin && (
            <div className="card" style={{ padding: '32px', textAlign: 'center', color: 'var(--text-muted)' }}>
              <p style={{ marginTop: '12px' }}>You need admin access to manage payment settings.</p>
            </div>
          )}
          {activeTab === 'PAYMENTS' && isAdmin && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Payment Methods</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Configure which payment options are available to your customers at the POS and online</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />

              <h4 style={{ margin: 0, fontSize: '0.95rem' }}>POS (In-Store) Payment Methods</h4>

              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                <PaymentMethodCard
                  icon="cash" label="Cash" description="Accept physical cash payments at the counter"
                  checked={acceptCash} onChange={setAcceptCash}
                />
                <PaymentMethodCard
                  icon="card" label="Card / POS Terminal" description="Credit and debit card payments via POS machine"
                  checked={acceptCard} onChange={setAcceptCard}
                />
              </div>

              <PaymentMethodCard
                icon="transfer" label="Bank Transfer" description="Direct bank transfers or wire payments"
                checked={acceptTransfer} onChange={setAcceptTransfer}
              />

              {acceptTransfer && (
                <div style={{
                  display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px',
                  padding: '16px', backgroundColor: 'var(--color-bg)', borderRadius: '10px',
                  border: '1px solid var(--color-border)',
                }}>
                  <TextInput label="Bank Name" value={bankName} onChange={e => setBankName(e.target.value)} placeholder="e.g. Qatar National Bank" />
                  <TextInput label="Account Name" value={bankAccountName} onChange={e => setBankAccountName(e.target.value)} placeholder="e.g. Dorzak Merchant" />
                  <TextInput label="Account Number" value={bankAccountNumber} onChange={e => setBankAccountNumber(e.target.value)} placeholder="e.g. 1234567890" />
                  <TextInput label="IBAN" value={bankIban} onChange={e => setBankIban(e.target.value)} placeholder="e.g. QA580244000000000000" />
                </div>
              )}

              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <h4 style={{ margin: 0, fontSize: '0.95rem' }}>Online Payment Methods</h4>

              <PaymentMethodCard
                icon="whatsapp" label="WhatsApp Order" description="Customers place orders via WhatsApp from your online catalog"
                checked={acceptWhatsapp || whatsappOrderingEnabled}
                onChange={(v) => { setAcceptWhatsapp(v); setWhatsappOrderingEnabled(v); }}
              />
              {whatsappOrderingEnabled && (
                <TextInput label="WhatsApp Number for Orders" value={whatsappPhone || accountInfo.whatsapp} onChange={e => setWhatsappPhone(e.target.value)} placeholder={accountInfo.whatsapp || '+974 XXXX XXXX'} />
              )}

              <PaymentMethodCard
                icon="transfer" label="Fawran Transfer" description="Accept payments via Fawran transfer from the online catalog"
                checked={fawranEnabled} onChange={setFawranEnabled}
              />
              {fawranEnabled && (
                <div style={{
                  display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px',
                  padding: '16px', backgroundColor: 'var(--color-bg)', borderRadius: '10px',
                  border: '1px solid var(--color-border)',
                }}>
                  <TextInput label="Fawran Alias" value={fawranAlias} onChange={e => setFawranAlias(e.target.value)} placeholder="e.g. Dorzak Merchant" />
                  <TextInput label="Fawran IBAN" value={fawranIban} onChange={e => setFawranIban(e.target.value)} placeholder="e.g. QA580244000000000000" />
                  <TextInput label="Fawran Mobile" value={fawranMobile} onChange={e => setFawranMobile(e.target.value)} placeholder="+974 XXXX XXXX" />
                </div>
              )}

              <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                <AppButton variant="primary" loading={payLoading} onClick={handleSavePayments}>
                  Save Payment Settings
                </AppButton>
              </div>
            </div>
          )}

          {/* INTEGRATIONS */}
          {activeTab === 'INTEGRATIONS' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Third-Party Integrations</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Connect your store to Facebook, Google Analytics, and other marketing tools</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />

              {/* Facebook Integration */}
              <div className="card" style={{ padding: '20px', backgroundColor: 'var(--color-bg)' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                  <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
                    <div style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: '#1877f2', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: '1.2rem' }}>f</div>
                    <div>
                      <strong>Facebook & Instagram Shop</strong>
                      <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Sync products and promote via Meta Social Commerce</div>
                    </div>
                  </div>
                  <AppButton
                    variant={facebookLinked ? 'danger' : 'secondary'}
                    type="button"
                    onClick={() => {
                      setFacebookLinked(!facebookLinked);
                      addToast(facebookLinked ? 'Facebook disconnected' : 'Facebook connected successfully!', facebookLinked ? 'info' : 'success');
                    }}
                  >
                    {facebookLinked ? 'Disconnect' : 'Connect Facebook'}
                  </AppButton>
                </div>
                {facebookLinked && (
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', backgroundColor: 'var(--dorzak-success-light)', padding: '10px', borderRadius: '6px', fontSize: '0.85rem', color: 'var(--color-success)' }}>
                    <AppIcon name="checkCircle" size={16} /> Connected to Facebook Page: Dorzak Merchant
                  </div>
                )}
              </div>

              {/* Facebook Pixel */}
              <div className="card" style={{ padding: '20px', backgroundColor: 'var(--color-bg)' }}>
                <div style={{ display: 'flex', gap: '12px', alignItems: 'flex-start', marginBottom: '12px' }}>
                  <div style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: '#0866ff', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: '0.85rem' }}>Px</div>
                  <div>
                    <strong>Facebook Pixel ID</strong>
                    <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Track customer actions on your catalog for Meta ad retargeting</div>
                  </div>
                </div>
                <TextInput
                  label="Facebook Pixel ID"
                  value={facebookPixelId}
                  onChange={e => setFacebookPixelId(e.target.value)}
                  placeholder="e.g. 123456789012345"
                />
              </div>

              {/* Google Analytics */}
              <div className="card" style={{ padding: '20px', backgroundColor: 'var(--color-bg)' }}>
                <div style={{ display: 'flex', gap: '12px', alignItems: 'flex-start', marginBottom: '12px' }}>
                  <div style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: '#e8710a', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 700, fontSize: '0.85rem' }}>GA</div>
                  <div>
                    <strong>Google Analytics 4</strong>
                    <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>Track catalog visitor data and conversion metrics with GA4</div>
                  </div>
                </div>
                <TextInput
                  label="Google Analytics Measurement ID"
                  value={googleAnalyticsId}
                  onChange={e => setGoogleAnalyticsId(e.target.value)}
                  placeholder="e.g. G-XXXXXXXXXX"
                />
              </div>
            </div>
          )}

          {/* LOYALTY (premium) */}
          {activeTab === 'LOYALTY' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Loyalty Program</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Reward repeat customers with points they earn on every order and redeem for discounts.</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <ToggleSwitch checked={loyaltyEnabled} onChange={setLoyaltyEnabled} label="Enable loyalty program" description="Customers earn points on completed orders." />
              {loyaltyEnabled && (
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px' }}>
                  <TextInput label="Points earned per 1 spent" type="number" min="0" value={loyaltyEarn} onChange={e => setLoyaltyEarn(e.target.value)} />
                  <TextInput label="Points to redeem" type="number" min="1" value={loyaltyRedeemPoints} onChange={e => setLoyaltyRedeemPoints(e.target.value)} />
                  <TextInput label="Discount value per redemption" type="number" step="0.01" min="0" value={loyaltyRedeemValue} onChange={e => setLoyaltyRedeemValue(e.target.value)} />
                </div>
              )}
              {isAdmin && (
                <div>
                  <AppButton variant="primary" type="button" loading={loyaltyLoading} onClick={handleSaveLoyalty}>Save loyalty settings</AppButton>
                </div>
              )}
            </div>
          )}

          {/* STAFF */}
          {activeTab === 'STAFF' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div>
                  <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Users & Staff Management</h3>
                  <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Invite staff members and assign role-based access permissions</p>
                </div>
                <AppButton variant="secondary" type="button" onClick={() => addToast('Staff invite email sent!', 'success')}>
                  + Invite Staff Member
                </AppButton>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />
              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {[
                  { name: ownerName, email, role: 'OWNER', badge: 'OWNER', color: 'var(--dorzak-primary)' },
                  { name: 'Alex Cashier', email: 'alex@example.com', role: 'Cashier (POS & Orders)', badge: 'CASHIER', color: 'var(--dorzak-warning)' },
                  { name: 'Maria Manager', email: 'maria@example.com', role: 'Manager (Full Access, no billing)', badge: 'MANAGER', color: 'var(--dorzak-success)' },
                ].map((staff, idx) => (
                  <div key={idx} style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    padding: '14px 16px',
                    backgroundColor: 'var(--color-bg)',
                    borderRadius: '10px',
                    border: '1px solid var(--color-border)',
                  }}>
                    <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
                      <div style={{
                        width: '38px', height: '38px', borderRadius: '50%',
                        backgroundColor: 'var(--dorzak-primary-light)', display: 'flex', alignItems: 'center',
                        justifyContent: 'center', fontWeight: 700, color: 'var(--dorzak-primary)', fontSize: '1rem'
                      }}>
                        {staff.name[0]}
                      </div>
                      <div>
                        <strong style={{ fontSize: '0.9rem' }}>{staff.name}{idx === 0 ? ' (You)' : ''}</strong>
                        <div style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>{staff.email} • {staff.role}</div>
                      </div>
                    </div>
                    <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
                      <span style={{ fontSize: '0.75rem', fontWeight: 700, padding: '3px 10px', borderRadius: '999px', backgroundColor: `${staff.color}22`, color: staff.color }}>
                        {staff.badge}
                      </span>
                      {idx > 0 && (
                        <button type="button" aria-label={`Remove ${staff.name}`} className="btn-icon" onClick={() => addToast('Staff member removed', 'info')}>
                          <AppIcon name="close" size={16} />
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* SUBSCRIPTION */}
          {activeTab === 'SUBSCRIPTION' && (
            <div className="card" style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
              <div>
                <h3 style={{ margin: '0 0 4px 0', fontSize: '1.1rem' }}>Subscription & Plan</h3>
                <p style={{ margin: 0, fontSize: '0.85rem', color: 'var(--text-muted)' }}>Manage your Dorzak Merchant plan, features, and billing cycle</p>
              </div>
              <hr style={{ border: 'none', borderTop: '1px solid var(--color-border)', margin: 0 }} />

              {subLoading ? (
                <div style={{ textAlign: 'center', padding: '40px', color: 'var(--text-muted)' }}>Loading subscription data...</div>
              ) : subData ? (
                <>
                  <div style={{
                    background: 'linear-gradient(135deg, var(--dorzak-primary) 0%, #6c3fc9 100%)',
                    borderRadius: '14px',
                    padding: '24px',
                    color: '#fff',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                  }}>
                    <div>
                      <div style={{ fontSize: '0.8rem', fontWeight: 600, opacity: 0.8, letterSpacing: '0.05em' }}>CURRENT PLAN</div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '1.8rem', fontWeight: 800, marginTop: '4px' }}>
                        Dorzak Merchant {subData.plan?.charAt(0).toUpperCase() + subData.plan?.slice(1).toLowerCase()}
                        <AppIcon name="star" size={24} />
                      </div>
                      <div style={{ fontSize: '0.85rem', opacity: 0.85, marginTop: '6px' }}>
                        {subData.renews_at ? `Renews ${new Date(subData.renews_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}` : ''}
                        {subData.billing_cycle ? ` • ${subData.billing_cycle.charAt(0).toUpperCase() + subData.billing_cycle.slice(1)} billing` : ''}
                      </div>
                    </div>
                    <div style={{ textAlign: 'right' }}>
                      <div style={{ fontSize: '2rem', fontWeight: 800 }}>{subData.price} {subData.currency || 'QAR'}</div>
                      <div style={{ fontSize: '0.8rem', opacity: 0.8 }}>per month</div>
                    </div>
                  </div>

                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px' }}>
                    {(subData.features ?? []).map((f: { feature: string; limit: number | null } | string, i: number) => {
                      const label = typeof f === 'string' ? f : f.feature.replace(/_/g, ' ');
                      const limit = typeof f === 'object' && f.limit != null ? ` (${f.limit})` : '';
                      return (
                        <div key={i} style={{
                          padding: '12px',
                          backgroundColor: 'var(--color-bg)',
                          borderRadius: '8px',
                          display: 'flex',
                          alignItems: 'center',
                          gap: '8px',
                          fontSize: '0.85rem',
                          fontWeight: 500,
                        }}>
                          <AppIcon name="check" size={16} color="var(--color-success)" />
                          {label}{limit}
                        </div>
                      );
                    })}
                  </div>

                  <div style={{ display: 'flex', gap: '12px' }}>
                    <AppButton type="button" variant="secondary" onClick={async () => {
                      try {
                        const res: any = await subscriptionApi.portal();
                        if (res.data?.url) window.open(res.data.url, '_blank');
                      } catch {
                        addToast('Billing portal coming soon', 'info');
                      }
                    }}>
                      Manage Billing
                    </AppButton>
                    <AppButton type="button" variant="secondary" onClick={() => addToast('Invoice download coming soon', 'info')}>
                      Download Invoice
                    </AppButton>
                    <AppButton type="button" variant="secondary" style={{ color: 'var(--dorzak-danger)' }} onClick={() => addToast('Please contact support to cancel', 'info')}>
                      Cancel Subscription
                    </AppButton>
                  </div>
                </>
              ) : (
                <div style={{ textAlign: 'center', padding: '40px', color: 'var(--text-muted)' }}>
                  Unable to load subscription data. <AppButton variant="secondary" type="button" onClick={() => setActiveTab('SUBSCRIPTION')}>Retry</AppButton>
                </div>
              )}
            </div>
          )}

          {/* Save Button (shown in all except subscription and payments — those save independently) */}
          {activeTab !== 'SUBSCRIPTION' && activeTab !== 'PAYMENTS' && (
            <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
              <AppButton variant="primary" type="submit" loading={loading}>Save Changes</AppButton>
            </div>
          )}
        </form>
      </div>
    </div>
  );
};

const PaymentMethodCard: React.FC<{
  icon: IconName; label: string; description: string;
  checked: boolean; onChange: (v: boolean) => void;
}> = ({ icon, label, description, checked, onChange }) => (
  <div style={{
    display: 'flex', justifyContent: 'space-between', alignItems: 'center',
    padding: '16px', backgroundColor: 'var(--color-bg)', borderRadius: '10px',
    border: `2px solid ${checked ? 'var(--dorzak-primary)' : 'var(--color-border)'}`,
  }}>
    <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
      <AppIcon name={icon} size={24} />
      <div>
        <strong style={{ fontSize: '0.9rem' }}>{label}</strong>
        <div style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{description}</div>
      </div>
    </div>
    <ToggleSwitch checked={checked} onChange={onChange} />
  </div>
);
