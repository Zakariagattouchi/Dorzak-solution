import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { expect, test } from 'vitest';
import { expectNoA11yViolations } from '../../test/axe';
import { CheckboxInput } from './CheckboxInput';
import { SelectInput } from './SelectInput';
import { TextInput } from './TextInput';
import { ToggleSwitch } from './ToggleSwitch';

function AccessibleForm() {
  const [online, setOnline] = useState(false);
  const [receipts, setReceipts] = useState(false);

  return (
    <form aria-label="Store preferences">
      <TextInput label="Name" error="Name is required" />
      <SelectInput
        label="Currency"
        defaultValue="QAR"
        options={[
          { value: 'QAR', label: 'QAR' },
          { value: 'USD', label: 'USD' },
        ]}
      />
      <ToggleSwitch checked={online} onChange={setOnline} label="Online store" />
      <CheckboxInput checked={receipts} onChange={setReceipts} label="Email receipts" />
    </form>
  );
}

test('associates labels and errors and supports keyboard boolean controls', async () => {
  const user = userEvent.setup();
  const { container } = render(<AccessibleForm />);
  const name = screen.getByLabelText('Name');
  expect(screen.getByLabelText('Currency')).toBeInTheDocument();
  const toggle = screen.getByLabelText('Online store');
  const checkbox = screen.getByLabelText('Email receipts');
  const errorId = name.getAttribute('aria-describedby');
  expect(errorId).toBeTruthy();
  expect(document.getElementById(errorId as string)).toHaveTextContent('Name is required');
  toggle.focus();
  await user.keyboard('[Space]');
  expect(toggle).toHaveAttribute('aria-checked', 'true');
  checkbox.focus();
  await user.keyboard('[Space]');
  expect(checkbox).toBeChecked();
  await expectNoA11yViolations(container);
});
