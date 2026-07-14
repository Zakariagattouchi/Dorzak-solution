import React, { useCallback, useEffect, useState } from 'react';
import { messagingApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { TextInput } from '../../../components/forms/TextInput';
import { SelectInput } from '../../../components/forms/SelectInput';
import { StatusPill } from '../../../components/feedback/StatusPill';
import { AppIcon } from '../../../components/icons/AppIcon';
import { useToastStore } from '../../../stores/toastStore';
import { TabIntro, apiErrorMessage } from '../marketingShared';

interface MessagingState {
  email_from_name: string;
  email_from_address: string;
  smtp_host: string;
  smtp_port: string;
  smtp_username: string;
  smtp_password: string; // write-only; blank = keep stored
  has_smtp_password: boolean;
  smtp_encryption: string;
  whatsapp_token: string; // write-only; blank = keep stored
  has_whatsapp_token: boolean;
  whatsapp_phone_number_id: string;
  whatsapp_display_number: string | null;
  whatsapp_connected: boolean;
  whatsapp_error: string | null;
}

const empty: MessagingState = {
  email_from_name: '',
  email_from_address: '',
  smtp_host: '',
  smtp_port: '',
  smtp_username: '',
  smtp_password: '',
  has_smtp_password: false,
  smtp_encryption: '',
  whatsapp_token: '',
  has_whatsapp_token: false,
  whatsapp_phone_number_id: '',
  whatsapp_display_number: null,
  whatsapp_connected: false,
  whatsapp_error: null,
};

/**
 * Channel setup: nothing sends until these are real. Email needs a sender
 * identity (and optionally the store's own SMTP); WhatsApp needs the store's
 * Business Cloud API credentials, verified live against the Graph API.
 */
export const ChannelsTab: React.FC<{ isAdmin: boolean }> = ({ isAdmin }) => {
  const { addToast } = useToastStore();
  const [s, setS] = useState<MessagingState>(empty);
  const [emailMode, setEmailMode] = useState<'unconfigured' | 'platform' | 'smtp'>('unconfigured');
  const [showSmtp, setShowSmtp] = useState(false);
  const [savingEmail, setSavingEmail] = useState(false);
  const [savingWa, setSavingWa] = useState(false);
  const [testing, setTesting] = useState(false);
  const [verifying, setVerifying] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = (await messagingApi.get()) as any;
      const m = res.messaging ?? {};
      setS({
        ...empty,
        ...m,
        smtp_port: m.smtp_port ? String(m.smtp_port) : '',
        smtp_password: '',
        whatsapp_token: '',
        smtp_encryption: m.smtp_encryption ?? '',
        email_from_name: m.email_from_name ?? '',
        email_from_address: m.email_from_address ?? '',
        smtp_host: m.smtp_host ?? '',
        smtp_username: m.smtp_username ?? '',
        whatsapp_phone_number_id: m.whatsapp_phone_number_id ?? '',
      });
      setEmailMode(res.status?.email?.mode ?? 'unconfigured');
      setShowSmtp(Boolean(m.smtp_host));
    } catch {
      /* keep defaults */
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const saveEmail = async () => {
    setSavingEmail(true);
    try {
      await messagingApi.update({
        email_from_name: s.email_from_name || null,
        email_from_address: s.email_from_address || null,
        smtp_host: showSmtp ? s.smtp_host || null : null,
        smtp_port: showSmtp && s.smtp_port ? Number(s.smtp_port) : null,
        smtp_username: showSmtp ? s.smtp_username || null : null,
        smtp_password: showSmtp ? s.smtp_password || null : null,
        smtp_encryption: showSmtp ? s.smtp_encryption || null : null,
      });
      addToast('Email channel saved.', 'success');
      await load();
    } catch (e) {
      addToast(apiErrorMessage(e), 'danger');
    } finally {
      setSavingEmail(false);
    }
  };

  const sendTest = async () => {
    setTesting(true);
    try {
      const res = (await messagingApi.testEmail()) as any;
      addToast(`Test email sent to ${res.to}. Check the inbox (and spam).`, 'success');
    } catch (e) {
      addToast(apiErrorMessage(e), 'danger');
    } finally {
      setTesting(false);
    }
  };

  const saveWhatsapp = async () => {
    setSavingWa(true);
    try {
      await messagingApi.update({
        whatsapp_token: s.whatsapp_token || null,
        whatsapp_phone_number_id: s.whatsapp_phone_number_id || null,
      });
      addToast('WhatsApp credentials saved — verify the connection next.', 'success');
      await load();
    } catch (e) {
      addToast(apiErrorMessage(e), 'danger');
    } finally {
      setSavingWa(false);
    }
  };

  const verify = async () => {
    setVerifying(true);
    try {
      const res = (await messagingApi.verifyWhatsapp()) as any;
      addToast(`Connected as ${res.display_phone_number}.`, 'success');
      await load();
    } catch (e) {
      const err = e as { data?: { error?: string } };
      addToast(err?.data?.error || apiErrorMessage(e), 'danger');
      await load();
    } finally {
      setVerifying(false);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro
        title="Channels"
        sub="Campaigns only send through channels that are actually set up and verified — configure them here once."
      />

      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))',
          gap: 14,
          alignItems: 'start',
        }}
      >
        {/* Email */}
        <div
          className="card"
          style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 14 }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h4 style={{ margin: 0, display: 'flex', alignItems: 'center', gap: 8 }}>
              <AppIcon name="mail" size={17} /> Email
            </h4>
            <StatusPill
              status={emailMode === 'unconfigured' ? 'INACTIVE' : 'ACTIVE'}
              label={
                emailMode === 'unconfigured'
                  ? 'Not set up'
                  : emailMode === 'smtp'
                    ? 'Custom SMTP'
                    : 'Platform mail'
              }
            />
          </div>
          <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>
            The identity your campaign emails are sent from. Use your own SMTP (e.g. your domain's
            mail provider) for best deliverability.
          </p>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <TextInput
              label="Sender name"
              value={s.email_from_name}
              onChange={(e) => setS({ ...s, email_from_name: e.target.value })}
              placeholder="Dorzak Merchant"
            />
            <TextInput
              label="Sender address"
              type="email"
              value={s.email_from_address}
              onChange={(e) => setS({ ...s, email_from_address: e.target.value })}
              placeholder="hello@yourstore.com"
            />
          </div>
          <label
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: 8,
              fontSize: '0.85rem',
              fontWeight: 600,
              cursor: 'pointer',
            }}
          >
            <input
              type="checkbox"
              checked={showSmtp}
              onChange={(e) => setShowSmtp(e.target.checked)}
            />{' '}
            Use my own SMTP server
          </label>
          {showSmtp && (
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
              <TextInput
                label="SMTP host"
                value={s.smtp_host}
                onChange={(e) => setS({ ...s, smtp_host: e.target.value })}
                placeholder="smtp.mailgun.org"
              />
              <TextInput
                label="Port"
                type="number"
                value={s.smtp_port}
                onChange={(e) => setS({ ...s, smtp_port: e.target.value })}
                placeholder="587"
              />
              <TextInput
                label="Username"
                value={s.smtp_username}
                onChange={(e) => setS({ ...s, smtp_username: e.target.value })}
              />
              <TextInput
                label="Password"
                type="password"
                value={s.smtp_password}
                onChange={(e) => setS({ ...s, smtp_password: e.target.value })}
                placeholder={s.has_smtp_password ? '•••••• (stored — leave blank to keep)' : ''}
              />
              <SelectInput
                label="Encryption"
                value={s.smtp_encryption}
                onChange={(e) => setS({ ...s, smtp_encryption: e.target.value })}
                options={[
                  { value: '', label: 'None' },
                  { value: 'tls', label: 'TLS' },
                  { value: 'ssl', label: 'SSL' },
                ]}
              />
            </div>
          )}
          {isAdmin && (
            <div style={{ display: 'flex', gap: 8 }}>
              <AppButton variant="primary" loading={savingEmail} onClick={saveEmail}>
                Save
              </AppButton>
              <AppButton
                variant="secondary"
                loading={testing}
                onClick={sendTest}
                disabled={emailMode === 'unconfigured'}
              >
                Send test email
              </AppButton>
            </div>
          )}
        </div>

        {/* WhatsApp */}
        <div
          className="card"
          style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 14 }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h4 style={{ margin: 0, display: 'flex', alignItems: 'center', gap: 8 }}>
              <AppIcon name="whatsapp" size={17} /> WhatsApp Business
            </h4>
            <StatusPill
              status={s.whatsapp_connected ? 'ACTIVE' : 'INACTIVE'}
              label={
                s.whatsapp_connected
                  ? `Connected${s.whatsapp_display_number ? ` · ${s.whatsapp_display_number}` : ''}`
                  : 'Not connected'
              }
            />
          </div>
          <p style={{ margin: 0, fontSize: '0.8rem', color: 'var(--text-muted)' }}>
            Connect your WhatsApp Business Cloud API number (from Meta's developer console): paste
            the permanent access token and the phone number ID, then verify.
          </p>
          <TextInput
            label="Access token"
            type="password"
            value={s.whatsapp_token}
            onChange={(e) => setS({ ...s, whatsapp_token: e.target.value })}
            placeholder={s.has_whatsapp_token ? '•••••• (stored — leave blank to keep)' : 'EAAG…'}
          />
          <TextInput
            label="Phone number ID"
            value={s.whatsapp_phone_number_id}
            onChange={(e) => setS({ ...s, whatsapp_phone_number_id: e.target.value })}
            placeholder="123456789012345"
          />
          {s.whatsapp_error && (
            <p
              style={{
                margin: 0,
                fontSize: '0.8rem',
                color: 'var(--dorzak-danger)',
                display: 'flex',
                gap: 6,
                alignItems: 'center',
              }}
            >
              <AppIcon name="alert" size={14} /> {s.whatsapp_error}
            </p>
          )}
          {isAdmin && (
            <div style={{ display: 'flex', gap: 8 }}>
              <AppButton variant="primary" loading={savingWa} onClick={saveWhatsapp}>
                Save
              </AppButton>
              <AppButton
                variant="secondary"
                loading={verifying}
                onClick={verify}
                disabled={!s.has_whatsapp_token && !s.whatsapp_token}
              >
                Verify connection
              </AppButton>
            </div>
          )}
          <p style={{ margin: 0, fontSize: '0.75rem', color: 'var(--text-muted)' }}>
            Marketing messages outside a 24-hour customer session require a Meta-approved template —
            set the template name on each campaign.
          </p>
        </div>
      </div>
    </div>
  );
};
