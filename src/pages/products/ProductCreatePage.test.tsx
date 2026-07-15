import { act, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, expect, test, vi } from 'vitest';
import { invalidateMerchantScope, MerchantScopeChangedError } from '../../stores/merchantScope';
import { ProductCreatePage } from './ProductCreatePage';

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
  addToast: vi.fn(),
  createProduct: vi.fn(),
  fetchCategories: vi.fn(),
  getProduct: vi.fn(),
  navigate: vi.fn(),
  translateArabic: vi.fn(),
  updateProduct: vi.fn(),
}));

vi.mock('../../stores/productStore', () => ({
  useProductStore: () => ({
    categories: [{ id: 'category-1', name: 'General', productCount: 0 }],
    createProduct: doubles.createProduct,
    fetchCategories: doubles.fetchCategories,
    getProduct: doubles.getProduct,
    updateProduct: doubles.updateProduct,
  }),
}));

vi.mock('../../stores/settingsStore', () => ({
  useSettingsStore: (selector: (state: { accountInfo: { currency: string } }) => unknown) =>
    selector({ accountInfo: { currency: 'QAR' } }),
}));

vi.mock('../../stores/toastStore', () => ({
  useToastStore: () => ({ addToast: doubles.addToast }),
}));

vi.mock('../../api/endpoints', () => ({
  catalogApi: { translateArabic: doubles.translateArabic },
}));

vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual<typeof import('react-router-dom')>('react-router-dom');
  return {
    ...actual,
    useNavigate: () => doubles.navigate,
    useParams: () => ({}),
  };
});

beforeEach(() => {
  vi.clearAllMocks();
  doubles.fetchCategories.mockResolvedValue(undefined);
  doubles.translateArabic.mockResolvedValue({ data: {} });
  doubles.updateProduct.mockResolvedValue(undefined);
});

async function startCreate(user: ReturnType<typeof userEvent.setup>, name: string) {
  await user.type(screen.getByLabelText('Product Name *'), name);
  await user.type(screen.getByLabelText('Selling Price (QAR) *'), '25');
  const saveButton = screen.getByRole('button', { name: 'Save Product' });
  await user.click(saveButton);
  await waitFor(() => expect(doubles.createProduct).toHaveBeenCalledTimes(1));
  return saveButton;
}

test('suppresses late product create success and scope errors after merchant replacement', async () => {
  const user = userEvent.setup();
  const lateSuccess = deferred<unknown>();
  doubles.createProduct.mockImplementationOnce(() => lateSuccess.promise);

  const view = render(<ProductCreatePage />);
  const successButton = await startCreate(user, 'Old merchant success');
  invalidateMerchantScope();
  await act(async () => {
    lateSuccess.resolve(undefined);
    await lateSuccess.promise;
  });

  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.navigate).not.toHaveBeenCalled();
  expect.soft(successButton).toBeDisabled();

  view.unmount();
  vi.clearAllMocks();
  const lateScopeError = deferred<unknown>();
  doubles.createProduct.mockImplementationOnce(() => lateScopeError.promise);

  render(<ProductCreatePage />);
  const errorButton = await startCreate(user, 'Old merchant error');
  invalidateMerchantScope();
  await act(async () => {
    lateScopeError.reject(new MerchantScopeChangedError());
    await lateScopeError.promise.catch(() => undefined);
  });

  expect.soft(doubles.addToast).not.toHaveBeenCalled();
  expect.soft(doubles.navigate).not.toHaveBeenCalled();
  expect.soft(errorButton).toBeDisabled();
});
