import { afterEach, expect, test, vi } from 'vitest';
import { getToken, request, setToken } from './apiClient';

afterEach(() => {
  setToken(null);
  vi.unstubAllGlobals();
  window.history.replaceState({}, '', '/');
});

test('normalizes API errors and clears an expired token', async () => {
  window.history.replaceState({}, '', '/login');
  const fetchMock = vi
    .fn()
    .mockResolvedValueOnce(
      new Response(
        JSON.stringify({
          message: 'Invalid order.',
          code: 'INVALID_ORDER',
          errors: { items: ['Required'] },
        }),
        { status: 422, headers: { 'Content-Type': 'application/json' } },
      ),
    )
    .mockResolvedValueOnce(
      new Response(
        JSON.stringify({
          message: 'Unauthenticated.',
          code: 'AUTH_REQUIRED',
        }),
        { status: 401, headers: { 'Content-Type': 'application/json' } },
      ),
    );
  vi.stubGlobal('fetch', fetchMock);

  await expect(request('/orders')).rejects.toMatchObject({
    status: 422,
    message: 'Invalid order.',
    code: 'INVALID_ORDER',
    errors: { items: ['Required'] },
  });
  setToken('expired');
  await expect(request('/auth/me')).rejects.toMatchObject({
    status: 401,
    message: 'Unauthenticated.',
  });
  expect(getToken()).toBeNull();
});
