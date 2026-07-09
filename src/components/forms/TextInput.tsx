import React from 'react';
import { AppIcon, IconName } from '../icons/AppIcon';

interface TextInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  icon?: IconName;
}

export const TextInput: React.FC<TextInputProps> = ({
  label,
  error,
  icon,
  className = '',
  ...props
}) => {
  return (
    <div className="form-group">
      {label && <label className="form-label">{label}</label>}
      <div style={{ position: 'relative', display: 'flex', alignItems: 'center' }}>
        {icon && (
          <div style={{ position: 'absolute', left: '12px', color: 'var(--text-muted)' }}>
            <AppIcon name={icon} size={16} />
          </div>
        )}
        <input
          className={`form-input ${className}`}
          style={{ paddingLeft: icon ? '36px' : '12px' }}
          {...props}
        />
      </div>
      {error && <span style={{ fontSize: '0.75rem', color: 'var(--dorzak-danger)' }}>{error}</span>}
    </div>
  );
};
