import React from 'react';

interface Option {
  value: string;
  label: string;
  disabled?: boolean;
}

interface SelectInputProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  options: Option[];
  error?: string;
}

export const SelectInput: React.FC<SelectInputProps> = ({
  label,
  options,
  error,
  id: providedId,
  className = '',
  ...props
}) => {
  const generatedId = React.useId();
  const inputId = providedId ?? generatedId;
  const errorId = error ? `${inputId}-error` : undefined;

  return (
    <div className="form-group">
      {label && (
        <label className="form-label" htmlFor={inputId}>
          {label}
        </label>
      )}
      <select
        id={inputId}
        className={`form-select ${className}`}
        aria-invalid={error ? true : undefined}
        aria-describedby={errorId}
        {...props}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value} disabled={option.disabled}>
            {option.label}
          </option>
        ))}
      </select>
      {error && (
        <span
          id={errorId}
          role="alert"
          style={{ fontSize: '0.75rem', color: 'var(--dorzak-danger)' }}
        >
          {error}
        </span>
      )}
    </div>
  );
};
