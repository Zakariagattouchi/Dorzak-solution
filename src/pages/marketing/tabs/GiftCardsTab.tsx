import React, { useCallback, useEffect, useState } from 'react';
import { giftCardsApi, customerApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { TextInput } from '../../../components/forms/TextInput';
import { SelectInput } from '../../../components/forms/SelectInput';
import { DataTable, Column } from '../../../components/tables/DataTable';
import { StatusPill } from '../../../components/feedback/StatusPill';
import { useToastStore } from '../../../stores/toastStore';
import { useMoney } from '../../../hooks/useMoney';
import { StatCard, TabIntro, apiErrorMessage } from '../marketingShared';

interface GiftCardRow {
  id: number;
  code: string;
  amount: number;
  status: 'active' | 'redeemed';
}
interface CustomerOption {
  id: number;
  name: string;
  phone?: string;
}

export const GiftCardsTab: React.FC<{ isAdmin: boolean }> = ({ isAdmin }) => {
  const { addToast } = useToastStore();
  const money = useMoney();
  const [cards, setCards] = useState<GiftCardRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [issuing, setIssuing] = useState(false);
  const [redeeming, setRedeeming] = useState(false);
  const [amount, setAmount] = useState('');
  const [lastIssued, setLastIssued] = useState<string | null>(null);
  const [redeemCode, setRedeemCode] = useState('');
  const [customerSearch, setCustomerSearch] = useState('');
  const [customers, setCustomers] = useState<CustomerOption[]>([]);
  const [customerId, setCustomerId] = useState('');

  const load = useCallback(async () => {
    try {
      setCards(((await giftCardsApi.list()) as any).gift_cards ?? []);
    } catch {
      /* locked */
    }
    setLoading(false);
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  // Debounced customer lookup for the redeem form.
  useEffect(() => {
    if (customerSearch.trim().length < 2) {
      setCustomers([]);
      return;
    }
    const t = setTimeout(async () => {
      try {
        const res = (await customerApi.list({ search: customerSearch.trim() })) as any;
        setCustomers((res.data ?? res.customers ?? []).slice(0, 8));
      } catch {
        /* ignore */
      }
    }, 350);
    return () => clearTimeout(t);
  }, [customerSearch]);

  const issue = async () => {
    if (!amount || Number(amount) <= 0) {
      addToast('Enter the gift card amount.', 'warning');
      return;
    }
    setIssuing(true);
    try {
      const res = (await giftCardsApi.issue(Number(amount))) as any;
      setLastIssued(res.code);
      setAmount('');
      addToast('Gift card issued — share the code with the buyer.', 'success');
      await load();
    } catch (e) {
      addToast(apiErrorMessage(e), 'danger');
    } finally {
      setIssuing(false);
    }
  };

  const redeem = async () => {
    if (!redeemCode.trim() || !customerId) {
      addToast('Enter the code and pick the customer.', 'warning');
      return;
    }
    setRedeeming(true);
    try {
      const res = (await giftCardsApi.redeem(redeemCode.trim(), Number(customerId))) as any;
      addToast(`Redeemed — customer's store credit is now ${money(res.balance)}.`, 'success');
      setRedeemCode('');
      setCustomerId('');
      setCustomerSearch('');
      setCustomers([]);
      await load();
    } catch (e) {
      addToast(apiErrorMessage(e), 'danger');
    } finally {
      setRedeeming(false);
    }
  };

  const outstanding = cards.filter((c) => c.status === 'active').reduce((s, c) => s + c.amount, 0);

  const columns: Column<GiftCardRow>[] = [
    {
      key: 'code',
      header: 'Code',
      render: (c) => <span style={{ fontFamily: 'monospace', fontWeight: 700 }}>{c.code}</span>,
    },
    { key: 'amount', header: 'Value', render: (c) => money(c.amount) },
    {
      key: 'status',
      header: 'Status',
      render: (c) => (
        <StatusPill
          status={c.status === 'active' ? 'ACTIVE' : 'COMPLETE'}
          label={c.status === 'active' ? 'Unredeemed' : 'Redeemed'}
        />
      ),
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro
        title="Gift cards"
        sub="Sell prepaid codes; redeeming one adds the value to a customer's store credit, spendable at checkout."
      />

      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))',
          gap: 12,
        }}
      >
        <StatCard
          label="Outstanding value"
          value={money(outstanding)}
          hint="sold, not yet redeemed"
        />
        <StatCard
          label="Cards redeemed"
          value={cards.filter((c) => c.status === 'redeemed').length}
          tone="success"
        />
      </div>

      {isAdmin && (
        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
            gap: 12,
          }}
        >
          <div
            className="card"
            style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 12 }}
          >
            <h4 style={{ margin: 0 }}>Issue a card</h4>
            <div style={{ display: 'flex', gap: 10, alignItems: 'flex-end' }}>
              <div style={{ flex: 1 }}>
                <TextInput
                  label="Amount"
                  type="number"
                  min="1"
                  value={amount}
                  onChange={(e) => setAmount(e.target.value)}
                />
              </div>
              <AppButton variant="primary" loading={issuing} onClick={issue}>
                Issue
              </AppButton>
            </div>
            {lastIssued && (
              <p style={{ margin: 0, fontSize: '0.9rem' }}>
                New code: <b style={{ fontFamily: 'monospace' }}>{lastIssued}</b>
              </p>
            )}
          </div>

          <div
            className="card"
            style={{ padding: 18, display: 'flex', flexDirection: 'column', gap: 12 }}
          >
            <h4 style={{ margin: 0 }}>Redeem for a customer</h4>
            <TextInput
              label="Card code"
              value={redeemCode}
              onChange={(e) => setRedeemCode(e.target.value.toUpperCase())}
              placeholder="GC-…"
            />
            <TextInput
              label="Find customer"
              value={customerSearch}
              onChange={(e) => setCustomerSearch(e.target.value)}
              placeholder="Name or phone…"
            />
            {customers.length > 0 && (
              <SelectInput
                label="Customer"
                value={customerId}
                onChange={(e) => setCustomerId(e.target.value)}
                options={[
                  { value: '', label: 'Choose…' },
                  ...customers.map((c) => ({
                    value: String(c.id),
                    label: c.phone ? `${c.name} (${c.phone})` : c.name,
                  })),
                ]}
              />
            )}
            <div>
              <AppButton variant="secondary" loading={redeeming} onClick={redeem}>
                Redeem to store credit
              </AppButton>
            </div>
          </div>
        </div>
      )}

      {loading ? (
        <div style={{ color: 'var(--text-muted)', padding: 30, textAlign: 'center' }}>
          Loading gift cards…
        </div>
      ) : cards.length === 0 ? (
        <div
          className="card"
          style={{ padding: 30, textAlign: 'center', color: 'var(--text-muted)' }}
        >
          No gift cards yet. Issue one when a customer buys it in-store; they (or the recipient)
          redeem it into store credit.
        </div>
      ) : (
        <DataTable
          columns={columns}
          data={cards}
          keyExtractor={(c) => String(c.id)}
          selectable={false}
        />
      )}
    </div>
  );
};
