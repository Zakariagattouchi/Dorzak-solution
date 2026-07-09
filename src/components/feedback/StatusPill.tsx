import React from 'react';

interface StatusPillProps {
  status: string;
  label?: string;
}

export const StatusPill: React.FC<StatusPillProps> = ({ status, label }) => {
  let variant: 'success' | 'warning' | 'danger' | 'primary' = 'primary';
  const text = label || status.toLowerCase().replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());

  switch (status.toUpperCase()) {
    case 'COMPLETE':
    case 'PAID':
    case 'ACTIVE':
      variant = 'success';
      break;
    case 'CONFIRMING':
    case 'PENDING_VERIFICATION':
    case 'UNPAID':
      variant = 'warning';
      break;
    case 'CANCELLED':
    case 'FAILED':
    case 'REFUNDED':
    case 'INACTIVE':
      variant = 'danger';
      break;
    case 'ACCEPTED':
    case 'PREPARING':
    case 'OUT_FOR_DELIVERY':
      variant = 'primary';
      break;
    default:
      variant = 'primary';
  }

  return <span className={`status-pill ${variant}`}>{text}</span>;
};
