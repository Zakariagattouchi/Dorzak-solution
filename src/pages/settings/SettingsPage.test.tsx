import { act, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, expect, test, vi } from 'vitest';
import { initialAccountInfo } from '../../data/mockData';
import { invalidateMerchantScope } from '../../stores/merchantScope';
import { SettingsPage } from './SettingsPage';

function deferred<T>() {
  let resolve!: (value: T | PromiseLike<T>) => void;
  let reject!: (reason?: unknown) => void;
  const promise = new Promise<T>((resolvePromise, rejectPromise) => {
    resolve = resolvePromise;
    reject = rejectPromise;
  });
  return { promise, resolve, reject };
}

const doubles = vi.hoisted(() => ({
  accountInfo: {} as typeof initialAccountInfo,
  addToast: vi.fn(),
  can: vi.fn(),
  loyaltyGet: vi.fn(),
  loyaltyUpdate: vi.fn(),
  setLanguage: vi.fn(),
  settingsGet: vi.fn(),
  settingsUpdate: vi.fn(),
  subscriptionGet: vi.fn(),
  subscriptionPortal: vi.fn(),
  updateSettings: vi.fn(),
}));

vi.mock('../../stores/settingsStore', () => ({
  useSettingsStore: () => ({
    accountInfo: doubles.accountInfo,
    setLanguage: doubles.setLanguage,
    updateSettings: doubles.updateSettings,
  }),
}));

vi.mock('../../stores/toastStore', () => ({
  useToastStore: () => ({ addToast: doubles.addToast }),
}));

vi.mock('../../stores/authStore', () => ({
  useAuthStore: (selector: (state: { can: typeof doubles.can }) => unknown) =>
    selector({ can: doubles.can }),
}));

vi.mock('../../api/endpoints', () => ({
  loyaltyApi: { get: doubles.loyaltyGet, update: doubles.loyaltyUpdate },
  settingsApi: { get: doubles.settingsGet, update: doubles.settingsUpdate },
  subscriptionApi: { get: doubles.subscriptionGet, portal: doubles.subscriptionPortal },
}));

vi.mock('../../components/forms/LocationPicker', () => ({
  LocationPicker: () => null,
}));

beforeEach(() => {
  vi.clearAllMocks();
  doubles.accountInfo = { ...initialAccountInfo, language: 'en' };
  doubles.can.mockReturnValue(true);
  doubles.loyaltyGet.mockResolvedValue({ loyalty: null });
  doubles.loyaltyUpdate.mockResolvedValue(undefined);
  doubles.settingsGet.mockResolvedValue({ data: {} });
  doubles.settingsUpdate.mockResolvedValue(undefined);
  doubles.subscriptionGet.mockResolvedValue({ data: {} });
  doubles.subscriptionPortal.mockResolvedValue(undefined);
  doubles.updateSettings.mockResolvedValue(undefined);
});

test('hydrates language and persists the sales-tax control', async () => {
  const user = userEvent.setup();
  const view = render(<SettingsPage />);
  expect(screen.getByLabelText('Interface Language')).toHaveValue('en');

  doubles.accountInfo = {
    ...initialAccountInfo,
    businessName: 'Arabic Store',
    chargeSalesTax: true,
    language: 'ar',
  };
  view.rerender(<SettingsPage />);
  await waitFor(() => expect(screen.getByLabelText('Interface Language')).toHaveValue('ar'));

  await user.click(screen.getByRole('button', { name: 'Save Changes' }));
  await waitFor(() => expect(doubles.updateSettings).toHaveBeenCalledTimes(1));
  expect(doubles.updateSettings).toHaveBeenLastCalledWith(
    expect.objectContaining({ language: 'ar' }),
  );
  expect(doubles.setLanguage).not.toHaveBeenCalled();

  await user.click(screen.getByRole('button', { name: 'Taxes' }));
  const chargeSalesTax = screen.getByRole('switch', { name: 'Charge Sales Taxes' });
  expect(chargeSalesTax).toHaveAttribute('aria-checked', 'true');
  await user.click(chargeSalesTax);
  expect(chargeSalesTax).toHaveAttribute('aria-checked', 'false');

  await user.click(screen.getByRole('button', { name: 'Save Changes' }));
  await waitFor(() => expect(doubles.updateSettings).toHaveBeenCalledTimes(2));
  expect(doubles.updateSettings).toHaveBeenLastCalledWith(
    expect.objectContaining({ chargeSalesTax: false, language: 'ar' }),
  );
});

test('payment save stops before storefront update after merchant replacement', async () => {
  const user = userEvent.setup();
  const paymentSave = deferred<undefined>();
  doubles.settingsUpdate.mockImplementationOnce(() => paymentSave.promise);

  render(<SettingsPage />);
  await user.click(screen.getByRole('button', { name: 'Payments' }));

  const saveButton = screen.getByRole('button', { name: 'Save Payment Settings' });
  await user.click(saveButton);
  await waitFor(() =>
    expect(doubles.settingsUpdate).toHaveBeenCalledWith('payments', expect.any(Object)),
  );

  invalidateMerchantScope();
  await act(async () => {
    paymentSave.resolve(undefined);
    await paymentSave.promise;
  });

  expect(saveButton).toHaveAttribute('type', 'button');
  expect(doubles.settingsUpdate).toHaveBeenCalledTimes(1);
  expect(doubles.addToast).not.toHaveBeenCalled();
});
