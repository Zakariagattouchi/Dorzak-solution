import React from 'react';
import { AppIcon, IconName } from '../icons/AppIcon';

interface AppButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'tertiary' | 'danger' | 'icon';
  icon?: IconName;
  loading?: boolean;
  children?: React.ReactNode;
}

export const AppButton: React.FC<AppButtonProps> = ({
  variant = 'primary',
  icon,
  loading = false,
  children,
  className = '',
  disabled,
  ...props
}) => {
  const btnClass = variant === 'icon' ? 'btn-icon' : `btn btn-${variant}`;

  return (
    <button
      className={`${btnClass} ${className}`}
      disabled={disabled || loading}
      {...props}
    >
      {loading ? (
        <span className="animate-spin" aria-hidden="true" />
      ) : (
        icon && <AppIcon name={icon} size={variant === 'icon' ? 18 : 16} />
      )}
      {children}
    </button>
  );
};
