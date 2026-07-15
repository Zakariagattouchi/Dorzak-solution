import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';
import { useState } from 'react';
import { expect, test } from 'vitest';
import { expectNoA11yViolations } from '../../test/axe';
import { DataTable } from '../tables/DataTable';
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

function CompactBooleanControls() {
  const [online, setOnline] = useState(false);

  return (
    <>
      <ToggleSwitch checked={online} onChange={setOnline} ariaLabel="Online catalog activation" />
      <DataTable
        columns={[{ key: 'name', header: 'Product' }]}
        data={[{ id: 'product-1', name: 'Coffee' }]}
        keyExtractor={(product) => product.id}
      />
    </>
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
  expect(checkbox.nextElementSibling).toHaveClass('checkbox-custom');
  const formsCssUrl = new URL('src/styles/forms.css', pathToFileURL(`${process.cwd()}/`));
  expect(readFileSync(formsCssUrl, 'utf8')).toContain(
    `.checkbox-wrapper > input:focus-visible + .checkbox-custom {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}`,
  );
  await user.keyboard('[Space]');
  expect(checkbox).toBeChecked();
  await expectNoA11yViolations(container);
});

test('names compact boolean controls without adding visible labels', async () => {
  const user = userEvent.setup();
  render(<CompactBooleanControls />);
  const online = screen.queryByRole('switch', { name: 'Online catalog activation' });
  const product = screen.queryByRole('checkbox', { name: 'Select row product-1' });

  expect.soft(online).toBeInTheDocument();
  expect.soft(product).toBeInTheDocument();
  expect(screen.queryByText('Online catalog activation')).not.toBeInTheDocument();
  expect(screen.queryByText('Select row product-1')).not.toBeInTheDocument();
  if (!online || !product) return;

  await user.click(online);
  expect(online).toHaveAttribute('aria-checked', 'true');
  await user.click(product);
  expect(product).toBeChecked();
});
