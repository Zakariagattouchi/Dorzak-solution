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
}) => {
  return (
    <label className={`checkbox-wrapper ${className}`} onClick={(e) => {
      e.stopPropagation();
      onChange(!checked);
    }}>
      <div className={`checkbox-custom ${checked ? 'checked' : ''}`}>
        {checked && <AppIcon name="check" size={12} color="#ffffff" />}
      </div>
      {label && <span className="form-label" style={{ margin: 0 }}>{label}</span>}
    </label>
  );
};
