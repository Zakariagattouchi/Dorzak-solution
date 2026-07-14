import React, { useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { useCustomerStore } from '../../stores/customerStore';
import { useOrderStore } from '../../stores/orderStore';
import { useModalStore } from '../../stores/modalStore';
import { useToastStore } from '../../stores/toastStore';
import { Customer } from '../../data/mockData';
import { TextInput } from '../../components/forms/TextInput';
import { AppButton } from '../../components/buttons/AppButton';
import { AppIcon } from '../../components/icons/AppIcon';
import { useMoney } from '../../hooks/useMoney';

type SortMode = 'A_Z' | 'Z_A' | 'BALANCE_ASC' | 'BALANCE_DESC' | 'NONE';

export const CustomersPage: React.FC = () => {
  const navigate = useNavigate();
  const { customers, deleteCustomer } = useCustomerStore();
  const { orders } = useOrderStore();
  const { openModal } = useModalStore();
  const { addToast } = useToastStore();
  const money = useMoney();

  const [search, setSearch] = useState('');
  const [sortMode, setSortMode] = useState<SortMode>('A_Z');
  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
  const [deleteConfirmId, setDeleteConfirmId] = useState<string | null>(null);

  const filtered = useMemo(() => {
    let list = customers.filter(
      (c) =>
        c.name.toLowerCase().includes(search.toLowerCase()) ||
        c.email.toLowerCase().includes(search.toLowerCase()) ||
        c.phone.toLowerCase().includes(search.toLowerCase()),
    );
    switch (sortMode) {
      case 'A_Z':
        return list.sort((a, b) => a.name.localeCompare(b.name));
      case 'Z_A':
        return list.sort((a, b) => b.name.localeCompare(a.name));
      case 'BALANCE_ASC':
        return list.sort((a, b) => a.totalSpent - b.totalSpent);
      case 'BALANCE_DESC':
        return list.sort((a, b) => b.totalSpent - a.totalSpent);
      default:
        return list;
    }
  }, [customers, search, sortMode]);

  const customerOrders = selectedCustomer
    ? orders.filter((o) => o.customerName === selectedCustomer.name)
    : [];

  const nameArrow = sortMode === 'A_Z' ? '↑' : sortMode === 'Z_A' ? '↓' : '';

  return (
    <div style={{ display: 'flex', gap: '24px' }}>
      {/* Delete Confirm Modal */}
      {deleteConfirmId && (
        <div
          style={{
            position: 'fixed',
            inset: 0,
            backgroundColor: 'rgba(0,0,0,0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 9999,
          }}
        >
          <div className="card" style={{ width: '400px', padding: '28px', textAlign: 'center' }}>
            <div style={{ fontSize: '1.3rem', fontWeight: 700, marginBottom: '12px' }}>
              Are you sure you want to delete this customer?
            </div>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.9rem', marginBottom: '24px' }}>
              This action cannot be undone. The customer's data will be permanently removed.
            </p>
            <div style={{ display: 'flex', gap: '12px', justifyContent: 'flex-end' }}>
              <AppButton variant="secondary" onClick={() => setDeleteConfirmId(null)}>
                Cancel
              </AppButton>
              <AppButton
                variant="danger"
                onClick={async () => {
                  await deleteCustomer(deleteConfirmId);
                  addToast('Customer deleted', 'info');
                  setDeleteConfirmId(null);
                  if (selectedCustomer?.id === deleteConfirmId) setSelectedCustomer(null);
                }}
              >
                Delete
              </AppButton>
            </div>
          </div>
        </div>
      )}

      {/* MAIN CONTENT */}
      <div style={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', gap: '20px' }}>
        {/* Header */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div>
            <h2 style={{ margin: 0 }}>Customers</h2>
            <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>
              {customers.length}{' '}
              {customers.length === 1 ? 'registered customer' : 'registered customers'}
            </span>
          </div>
          <div style={{ display: 'flex', gap: '10px' }}>
            <AppButton
              variant="secondary"
              onClick={() => addToast('Exporting customers CSV...', 'info')}
            >
              ↑ Export
            </AppButton>
            <AppButton
              variant="secondary"
              onClick={() => addToast('Import modal coming soon', 'info')}
            >
              ↓ Import Customers
            </AppButton>
            <AppButton variant="primary" onClick={() => openModal('CUSTOMER_CREATE')}>
              + Register Customer
            </AppButton>
          </div>
        </div>

        {/* Summary Cards */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '12px' }}>
          <div className="card" style={{ padding: '12px' }}>
            <div
              style={{
                fontSize: '0.72rem',
                color: 'var(--text-muted)',
                fontWeight: 700,
                letterSpacing: '0.04em',
              }}
            >
              TOTAL CUSTOMERS
            </div>
            <div style={{ fontSize: '1.4rem', fontWeight: 800, color: 'var(--dorzak-primary)' }}>
              {customers.length}
            </div>
          </div>
          <div className="card" style={{ padding: '12px' }}>
            <div
              style={{
                fontSize: '0.72rem',
                color: 'var(--text-muted)',
                fontWeight: 700,
                letterSpacing: '0.04em',
              }}
            >
              TOTAL SPENT
            </div>
            <div style={{ fontSize: '1.4rem', fontWeight: 800, color: 'var(--dorzak-success)' }}>
              {money(customers.reduce((s, c) => s + c.totalSpent, 0))}
            </div>
          </div>
          <div className="card" style={{ padding: '12px' }}>
            <div
              style={{
                fontSize: '0.72rem',
                color: 'var(--text-muted)',
                fontWeight: 700,
                letterSpacing: '0.04em',
              }}
            >
              AVG LIFETIME VALUE
            </div>
            <div style={{ fontSize: '1.4rem', fontWeight: 800, color: 'var(--dorzak-warning)' }}>
              {money(
                customers.length > 0
                  ? customers.reduce((s, c) => s + c.totalSpent, 0) / customers.length
                  : 0,
              )}
            </div>
          </div>
        </div>

        {/* Search */}
        <TextInput
          placeholder="Search customers by name, email or phone..."
          icon="search"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />

        {/* Table */}
        <div className="card" style={{ padding: 0, overflow: 'hidden' }}>
          {/* Table Header */}
          <div
            style={{
              display: 'grid',
              gridTemplateColumns: '3fr 2fr 2fr 1fr 1fr 1fr 80px',
              padding: '10px 16px',
              borderBottom: '2px solid var(--color-border)',
              fontSize: '0.75rem',
              fontWeight: 700,
              color: 'var(--text-muted)',
              textTransform: 'uppercase',
              letterSpacing: '0.04em',
            }}
          >
            <button
              type="button"
              onClick={() => setSortMode(sortMode === 'A_Z' ? 'Z_A' : 'A_Z')}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '4px',
                fontWeight: 700,
                fontSize: '0.75rem',
                color: 'var(--text-muted)',
                textTransform: 'uppercase',
                letterSpacing: '0.04em',
              }}
            >
              NAME{' '}
              {nameArrow && <span style={{ color: 'var(--dorzak-primary)' }}>{nameArrow}</span>}
            </button>
            <span>PHONE</span>
            <span>EMAIL</span>
            <span>BALANCE</span>
            <span>NOTES</span>
            <span>ADDRESS</span>
            <span></span>
          </div>

          {/* Rows */}
          {filtered.length === 0 ? (
            <div style={{ padding: '48px', textAlign: 'center', color: 'var(--text-muted)' }}>
              {search ? (
                <>
                  <div style={{ fontSize: '1.1rem', fontWeight: 600, marginBottom: '8px' }}>
                    Search not found
                  </div>
                  <div style={{ fontSize: '0.85rem' }}>
                    Try to search again with different terms
                  </div>
                </>
              ) : (
                <>
                  <div style={{ fontSize: '1.1rem', fontWeight: 600, marginBottom: '8px' }}>
                    Let's register your first customer
                  </div>
                  <AppButton variant="primary" onClick={() => openModal('CUSTOMER_CREATE')}>
                    + Register Customer
                  </AppButton>
                </>
              )}
            </div>
          ) : (
            filtered.map((c) => (
              <div
                key={c.id}
                onClick={() => setSelectedCustomer(c)}
                style={{
                  display: 'grid',
                  gridTemplateColumns: '3fr 2fr 2fr 1fr 1fr 1fr 80px',
                  padding: '14px 16px',
                  borderBottom: '1px solid var(--color-border)',
                  cursor: 'pointer',
                  alignItems: 'center',
                  backgroundColor:
                    selectedCustomer?.id === c.id ? 'var(--dorzak-primary-light)' : 'transparent',
                  transition: 'background 0.12s',
                }}
              >
                {/* Name + Avatar */}
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                  <div
                    style={{
                      width: '36px',
                      height: '36px',
                      borderRadius: '50%',
                      backgroundColor: 'var(--dorzak-primary-light)',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontWeight: 800,
                      color: 'var(--dorzak-primary)',
                      fontSize: '0.95rem',
                      flexShrink: 0,
                    }}
                  >
                    {c.name[0]}
                  </div>
                  <span
                    style={{
                      fontWeight: 600,
                      fontSize: '0.9rem',
                      overflow: 'hidden',
                      textOverflow: 'ellipsis',
                      whiteSpace: 'nowrap',
                    }}
                  >
                    {c.name}
                  </span>
                </div>

                {/* Phone (WhatsApp link) */}
                <a
                  href={`https://wa.me/${c.phone.replace(/\D/g, '')}`}
                  target="_blank"
                  rel="noreferrer"
                  onClick={(e) => e.stopPropagation()}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '4px',
                    color: '#25d366',
                    fontSize: '0.85rem',
                    fontWeight: 500,
                  }}
                >
                  <AppIcon name="whatsapp" size={16} />
                  {c.phone}
                </a>

                {/* Email */}
                <span
                  style={{
                    fontSize: '0.85rem',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    whiteSpace: 'nowrap',
                    color: 'var(--text-muted)',
                  }}
                >
                  {c.email}
                </span>

                {/* Balance */}
                <span
                  style={{
                    fontWeight: 700,
                    color: c.totalSpent > 0 ? 'var(--dorzak-success)' : 'var(--text-muted)',
                    fontSize: '0.9rem',
                  }}
                >
                  {money(c.totalSpent)}
                </span>

                {/* Notes */}
                {c.notes ? (
                  <span
                    title={c.notes}
                    style={{ display: 'inline-flex', cursor: 'help', color: 'var(--text-muted)' }}
                  >
                    <AppIcon name="note" size={16} />
                  </span>
                ) : (
                  <span />
                )}

                {/* Address */}
                {c.address ? (
                  <a
                    href={`https://maps.google.com?q=${encodeURIComponent(c.address)}`}
                    target="_blank"
                    rel="noreferrer"
                    onClick={(e) => e.stopPropagation()}
                    title={c.address}
                    style={{ color: 'var(--text-muted)', fontSize: '0.9rem' }}
                  >
                    <AppIcon name="mapPin" size={16} />
                  </a>
                ) : (
                  <span />
                )}

                {/* Delete */}
                <button
                  type="button"
                  onClick={(e) => {
                    e.stopPropagation();
                    setDeleteConfirmId(c.id);
                  }}
                  style={{ color: 'var(--text-muted)', padding: '4px', borderRadius: '4px' }}
                >
                  <AppIcon name="trash" size={15} color="var(--text-muted)" />
                </button>
              </div>
            ))
          )}
        </div>
      </div>

      {/* CUSTOMER DETAIL SIDE PANEL */}
      {selectedCustomer && (
        <div
          className="card"
          style={{
            width: '280px',
            flexShrink: 0,
            display: 'flex',
            flexDirection: 'column',
            gap: '16px',
            alignSelf: 'flex-start',
            position: 'sticky',
            top: '16px',
            maxHeight: 'calc(100vh - 80px)',
            overflowY: 'auto',
          }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <strong style={{ fontSize: '0.85rem' }}>Customer Detail</strong>
            <button
              type="button"
              className="btn-icon"
              aria-label="Close customer detail"
              onClick={() => setSelectedCustomer(null)}
            >
              <AppIcon name="close" size={16} />
            </button>
          </div>

          <div
            style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: '8px',
              padding: '12px 0',
              borderBottom: '1px solid var(--color-border)',
            }}
          >
            <div
              style={{
                width: '60px',
                height: '60px',
                borderRadius: '50%',
                backgroundColor: 'var(--dorzak-primary-light)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontWeight: 800,
                color: 'var(--dorzak-primary)',
                fontSize: '1.5rem',
              }}
            >
              {selectedCustomer.name[0]}
            </div>
            <strong style={{ fontSize: '1rem', textAlign: 'center' }}>
              {selectedCustomer.name}
            </strong>
            <span style={{ fontSize: '0.78rem', color: 'var(--text-muted)' }}>
              {selectedCustomer.city || '—'}
            </span>
          </div>

          <div
            style={{ display: 'flex', flexDirection: 'column', gap: '10px', fontSize: '0.85rem' }}
          >
            {[
              { label: 'Email', value: selectedCustomer.email },
              { label: 'Phone', value: selectedCustomer.phone },
              { label: 'Address', value: selectedCustomer.address || '—' },
              { label: 'Total Orders', value: selectedCustomer.totalOrders.toString() },
              { label: 'Total Spent', value: money(selectedCustomer.totalSpent) },
            ].map((row) => (
              <div
                key={row.label}
                style={{ display: 'flex', justifyContent: 'space-between', gap: '8px' }}
              >
                <span style={{ color: 'var(--text-muted)', flexShrink: 0 }}>{row.label}</span>
                <span
                  style={{
                    textAlign: 'right',
                    fontWeight: 600,
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                  }}
                >
                  {row.value}
                </span>
              </div>
            ))}
            {selectedCustomer.notes && (
              <div
                style={{
                  padding: '8px',
                  backgroundColor: 'var(--color-bg)',
                  borderRadius: '6px',
                  fontSize: '0.8rem',
                  color: 'var(--text-muted)',
                }}
              >
                <AppIcon name="note" size={15} /> {selectedCustomer.notes}
              </div>
            )}
          </div>

          {customerOrders.length > 0 && (
            <div>
              <strong
                style={{
                  fontSize: '0.78rem',
                  color: 'var(--text-muted)',
                  letterSpacing: '0.04em',
                  textTransform: 'uppercase',
                }}
              >
                Recent Orders
              </strong>
              <div
                style={{ display: 'flex', flexDirection: 'column', gap: '6px', marginTop: '8px' }}
              >
                {customerOrders.slice(0, 3).map((o) => (
                  <div
                    key={o.id}
                    style={{
                      display: 'flex',
                      justifyContent: 'space-between',
                      padding: '6px 10px',
                      backgroundColor: 'var(--color-bg)',
                      borderRadius: '6px',
                      fontSize: '0.8rem',
                    }}
                  >
                    <span style={{ color: 'var(--dorzak-primary)', fontWeight: 600 }}>{o.id}</span>
                    <strong style={{ color: 'var(--dorzak-success)' }}>{money(o.total)}</strong>
                  </div>
                ))}
              </div>
            </div>
          )}

          <a
            href={`https://wa.me/${selectedCustomer.phone.replace(/\D/g, '')}`}
            target="_blank"
            rel="noreferrer"
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '8px',
              backgroundColor: '#25d366',
              color: '#fff',
              borderRadius: '8px',
              padding: '10px',
              fontWeight: 700,
              fontSize: '0.9rem',
              textDecoration: 'none',
            }}
          >
            <AppIcon name="whatsapp" size={16} /> Send WhatsApp Message
          </a>

          <AppButton
            variant="secondary"
            onClick={() => setDeleteConfirmId(selectedCustomer.id)}
            style={{ color: 'var(--dorzak-danger)' }}
          >
            Delete Customer
          </AppButton>
        </div>
      )}
    </div>
  );
};
