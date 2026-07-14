import React from 'react';
import { AppIcon } from '../icons/AppIcon';

interface CheckboxInputProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  label?: string;
  className?: string;
}

export const CheckboxInput: React.FC<CheckboxInputProps> = ({
  checked,
  onChange,
  label,
  className = '',
}) => (
<label
  className={`checkbox-wrapper ${className}`}
  onClick={(event) => event.stopPropagation()}
>
  <input
    type="checkbox"
    checked={checked}
    onChange={(event) => onChange(event.target.checked)}
    style={{
      position: 'absolute',
      width: 1,
      height: 1,
      overflow: 'hidden',
      clip: 'rect(0 0 0 0)',
      clipPath: 'inset(50%)',
      whiteSpace: 'nowrap',
    }}
  />
  <span aria-hidden="true" className={`checkbox-custom ${checked ? 'checked' : ''}`}>
    {checked && <AppIcon name="check" size={12} color="#ffffff" />}
  </span>
  {label && <span className="form-label" style={{ margin: 0 }}>{label}</span>}
</label>
);
