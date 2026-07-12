import React, { useCallback, useEffect, useState } from 'react';
import { reviewsApi } from '../../../api/endpoints';
import { AppButton } from '../../../components/buttons/AppButton';
import { DataTable, Column } from '../../../components/tables/DataTable';
import { StatusPill } from '../../../components/feedback/StatusPill';
import { useToastStore } from '../../../stores/toastStore';
import { StatCard, TabIntro, ConfirmButton, apiErrorMessage } from '../marketingShared';

interface ReviewRow {
  id: number;
  product_id: number;
  product_name: string | null;
  author_name: string | null;
  rating: number;
  comment: string | null;
  approved: boolean;
  created_at: string | null;
}

const Stars: React.FC<{ rating: number }> = ({ rating }) => (
  <span aria-label={`${rating} out of 5`} style={{ color: 'var(--dorzak-warning, #d97706)', letterSpacing: 1 }}>
    {'★'.repeat(rating)}<span style={{ color: 'var(--color-border)' }}>{'★'.repeat(5 - rating)}</span>
  </span>
);

export const ReviewsTab: React.FC<{ isAdmin: boolean }> = ({ isAdmin }) => {
  const { addToast } = useToastStore();
  const [reviews, setReviews] = useState<ReviewRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'PENDING' | 'ALL'>('PENDING');

  const load = useCallback(async () => {
    try { setReviews(((await reviewsApi.list()) as any).reviews ?? []); } catch { /* locked */ }
    setLoading(false);
  }, []);

  useEffect(() => { load(); }, [load]);

  const approve = async (r: ReviewRow) => {
    try { await reviewsApi.approve(r.id); addToast('Review published.', 'success'); await load(); } catch (e) { addToast(apiErrorMessage(e), 'danger'); }
  };

  const remove = async (r: ReviewRow) => {
    try { await reviewsApi.remove(r.id); addToast('Review removed.', 'success'); await load(); } catch (e) { addToast(apiErrorMessage(e), 'danger'); }
  };

  const pending = reviews.filter(r => !r.approved);
  const approved = reviews.filter(r => r.approved);
  const average = approved.length ? approved.reduce((s, r) => s + r.rating, 0) / approved.length : 0;
  const visible = filter === 'PENDING' ? pending : reviews;

  const columns: Column<ReviewRow>[] = [
    { key: 'product', header: 'Product', render: r => <b>{r.product_name ?? '—'}</b> },
    { key: 'rating', header: 'Rating', render: r => <Stars rating={r.rating} /> },
    { key: 'comment', header: 'Comment', render: r => <span style={{ fontSize: '0.85rem' }}>{r.comment || <em style={{ color: 'var(--text-muted)' }}>No comment</em>}</span> },
    { key: 'author', header: 'By', render: r => <span style={{ fontSize: '0.85rem', color: 'var(--text-muted)' }}>{r.author_name || 'Anonymous'}</span> },
    { key: 'status', header: 'Status', render: r => <StatusPill status={r.approved ? 'ACTIVE' : 'CONFIRMING'} label={r.approved ? 'Published' : 'Pending'} /> },
    ...(isAdmin ? [{
      key: 'actions', header: '', render: (r: ReviewRow) => (
        <span style={{ display: 'flex', gap: 4, justifyContent: 'flex-end' }} onClick={e => e.stopPropagation()}>
          {!r.approved && <AppButton variant="secondary" onClick={() => approve(r)}>Publish</AppButton>}
          <ConfirmButton label="Remove" onConfirm={() => remove(r)} />
        </span>
      ),
    }] : []),
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <TabIntro title="Product reviews" sub="Reviews wait here until you publish them — nothing appears on your storefront unmoderated.">
        <div style={{ display: 'flex', gap: 6 }}>
          <AppButton variant={filter === 'PENDING' ? 'primary' : 'tertiary'} onClick={() => setFilter('PENDING')}>Pending{pending.length ? ` (${pending.length})` : ''}</AppButton>
          <AppButton variant={filter === 'ALL' ? 'primary' : 'tertiary'} onClick={() => setFilter('ALL')}>All</AppButton>
        </div>
      </TabIntro>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12 }}>
        <StatCard label="Average rating" value={approved.length ? average.toFixed(1) : '—'} tone="success" hint={`${approved.length} published`} />
        <StatCard label="Awaiting moderation" value={pending.length} tone={pending.length ? 'danger' : 'muted'} />
      </div>

      {loading ? (
        <div style={{ color: 'var(--text-muted)', padding: 30, textAlign: 'center' }}>Loading reviews…</div>
      ) : visible.length === 0 ? (
        <div className="card" style={{ padding: 30, textAlign: 'center', color: 'var(--text-muted)' }}>
          {filter === 'PENDING' ? 'No reviews waiting — all caught up.' : 'No reviews yet. They arrive here when customers rate products.'}
        </div>
      ) : (
        <DataTable columns={columns} data={visible} keyExtractor={r => String(r.id)} selectable={false} />
      )}
    </div>
  );
};
