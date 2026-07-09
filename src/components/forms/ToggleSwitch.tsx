import React from 'react';

interface ToggleSwitchProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  label?: string;
  description?: string;
}

export const ToggleSwitch: React.FC<ToggleSwitchProps> = ({
  checked,
  onChange,
  label,
  description,
}) => {
  return (
    <div
      className="toggle-wrapper"
      onClick={() => onChange(!checked)}
      role="switch"
      aria-checked={checked}
    >
      {(label || description) && (
        <div className="flex flex-col">
          {label && <span className="form-label">{label}</span>}
          {description && <span style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>{description}</span>}
        </div>
      )}
      <div className={`toggle-switch ${checked ? 'active' : ''}`}>
        <div className="toggle-thumb" />
      </div>
    </div>
  );
};
