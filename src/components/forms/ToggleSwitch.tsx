import React from 'react';

type AccessibleNameProps =
  | { label: string; ariaLabel?: string }
  | { label?: undefined; ariaLabel: string };

type ToggleSwitchProps = {
  checked: boolean;
  onChange: (checked: boolean) => void;
  description?: string;
} & AccessibleNameProps;

export const ToggleSwitch: React.FC<ToggleSwitchProps> = ({
  checked,
  onChange,
  label,
  ariaLabel,
  description,
}) => {
  const generatedId = React.useId();
  const descriptionId = description ? `${generatedId}-description` : undefined;

  return (
    <div className="toggle-wrapper">
      {(label || description) && (
        <div className="flex flex-col">
          {label && <span className="form-label">{label}</span>}
          {description && (
            <span id={descriptionId} style={{ fontSize: '0.8rem', color: 'var(--text-muted)' }}>
              {description}
            </span>
          )}
        </div>
      )}
      <button
        type="button"
        role="switch"
        aria-checked={checked}
        aria-label={ariaLabel ?? label}
        aria-describedby={descriptionId}
        onClick={() => onChange(!checked)}
        className={`toggle-switch ${checked ? 'active' : ''}`}
      >
        <span aria-hidden="true" className="toggle-thumb" />
      </button>
    </div>
  );
};
