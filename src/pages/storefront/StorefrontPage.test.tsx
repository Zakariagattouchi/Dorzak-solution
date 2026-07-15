import { act, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, expect, test, vi } from 'vitest';
import { initialAccountInfo } from '../../data/mockData';
import { invalidateMerchantScope } from '../../stores/merchantScope';
import { StorefrontPage } from './StorefrontPage';

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
  fetchSettings: vi.fn(),
  navigate: vi.fn(),
  subscriptionGet: vi.fn(),
  toggleOnlineStore: vi.fn(),
  updateSettings: vi.fn(),
  uploadStorefront: vi.fn(),
}));

vi.mock('../../stores/settingsStore', () => ({
  useSettingsStore: () => ({
    accountInfo: doubles.accountInfo,
    fetchSettings: doubles.fetchSettings,
    toggleOnlineStore: doubles.toggleOnlineStore,
    updateSettings: doubles.updateSettings,
  }),
}));

vi.mock('../../stores/toastStore', () => ({
  useToastStore: () => ({ addToast: doubles.addToast }),
}));

vi.mock('../../api/endpoints', () => ({
  settingsApi: { uploadStorefront: doubles.uploadStorefront },
  subscriptionApi: { get: doubles.subscriptionGet },
}));

vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual<typeof import('react-router-dom')>('react-router-dom');
  return { ...actual, useNavigate: () => doubles.navigate };
});

beforeEach(() => {
  vi.clearAllMocks();
  doubles.accountInfo = { ...initialAccountInfo, latitude: 25.3, longitude: 51.5 };
  doubles.fetchSettings.mockResolvedValue(undefined);
  doubles.subscriptionGet.mockResolvedValue({ data: { plan_is_default: false } });
  doubles.toggleOnlineStore.mockResolvedValue(undefined);
  doubles.updateSettings.mockResolvedValue(undefined);
  doubles.uploadStorefront.mockResolvedValue(undefined);
  Object.defineProperty(URL, 'createObjectURL', {
    configurable: true,
    value: vi.fn(() => 'blob:test-preview'),
  });
});

test('storefront save stops before next upload or settings update after merchant replacement', async () => {
  const user = userEvent.setup();
  const bannerUpload = deferred<undefined>();
  doubles.uploadStorefront.mockImplementationOnce(() => bannerUpload.promise);

  const view = render(<StorefrontPage />);
  await user.click(screen.getByRole('button', { name: 'Branding & Banner' }));
  const fileInputs = view.container.querySelectorAll<HTMLInputElement>('input[type="file"]');
  expect(fileInputs).toHaveLength(2);
  await user.upload(fileInputs[0], new File(['banner'], 'banner.png', { type: 'image/png' }));
  await user.upload(fileInputs[1], new File(['logo'], 'logo.png', { type: 'image/png' }));

  await user.click(screen.getByRole('button', { name: 'Save Storefront Options' }));
  await waitFor(() => expect(doubles.uploadStorefront).toHaveBeenCalledTimes(1));
  expect(doubles.uploadStorefront).toHaveBeenCalledWith('banner', expect.any(File));

  invalidateMerchantScope();
  await act(async () => {
    bannerUpload.resolve(undefined);
    await bannerUpload.promise;
  });

  expect(doubles.uploadStorefront).toHaveBeenCalledTimes(1);
  expect(doubles.updateSettings).not.toHaveBeenCalled();
  expect(doubles.fetchSettings).not.toHaveBeenCalled();
  expect(doubles.addToast).not.toHaveBeenCalled();
});

test('late storefront refresh produces no toast or billing navigation', async () => {
  const user = userEvent.setup();
  const refresh = deferred<undefined>();
  doubles.fetchSettings.mockImplementationOnce(() => refresh.promise);

  const view = render(<StorefrontPage />);
  await user.click(screen.getByRole('button', { name: 'Save Storefront Options' }));
  await waitFor(() => expect(doubles.fetchSettings).toHaveBeenCalledTimes(1));

  invalidateMerchantScope();
  await act(async () => {
    refresh.resolve(undefined);
    await refresh.promise;
  });

  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.navigate).not.toHaveBeenCalledWith('/billing');

  view.unmount();
  vi.clearAllMocks();
  const rejectedRefresh = deferred<undefined>();
  doubles.fetchSettings.mockImplementationOnce(() => rejectedRefresh.promise);

  render(<StorefrontPage />);
  await user.click(screen.getByRole('button', { name: 'Save Storefront Options' }));
  await waitFor(() => expect(doubles.fetchSettings).toHaveBeenCalledTimes(1));

  invalidateMerchantScope();
  await act(async () => {
    rejectedRefresh.reject({ code: 'PLAN_UPGRADE_REQUIRED' });
    await rejectedRefresh.promise.catch(() => undefined);
  });

  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.navigate).not.toHaveBeenCalledWith('/billing');
});

test('storefront labels fulfillment amounts with the configured ISO currency', async () => {
  const user = userEvent.setup();
  doubles.accountInfo = {
    ...initialAccountInfo,
    currency: 'QAR',
    latitude: 25.3,
    longitude: 51.5,
  };

  const view = render(<StorefrontPage />);
  await user.click(screen.getByRole('button', { name: 'Delivery, Pickup & Dine-in' }));

  expect.soft(screen.queryByLabelText('Standard Delivery Fee (QAR)')).toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Free Delivery Threshold (QAR)')).toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Minimum Order Value (QAR)')).toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Standard Delivery Fee ($)')).not.toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Free Delivery Threshold ($)')).not.toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Minimum Order Value ($)')).not.toBeInTheDocument();

  view.unmount();
  doubles.accountInfo = {
    ...initialAccountInfo,
    currency: 'BHD',
    latitude: 25.3,
    longitude: 51.5,
  };
  render(<StorefrontPage />);
  await user.click(screen.getByRole('button', { name: 'Delivery, Pickup & Dine-in' }));

  expect.soft(screen.queryByLabelText('Standard Delivery Fee (BHD)')).toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Free Delivery Threshold (BHD)')).toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Minimum Order Value (BHD)')).toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Standard Delivery Fee (QAR)')).not.toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Free Delivery Threshold (QAR)')).not.toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Minimum Order Value (QAR)')).not.toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Standard Delivery Fee ($)')).not.toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Free Delivery Threshold ($)')).not.toBeInTheDocument();
  expect.soft(screen.queryByLabelText('Minimum Order Value ($)')).not.toBeInTheDocument();
});
