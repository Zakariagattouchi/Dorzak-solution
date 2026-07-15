import { afterEach, expect, test, vi } from 'vitest';
import { getToken, request, setToken } from './apiClient';
import { authApi } from './endpoints';

function deferred<T>() {
  let resolve!: (value: T | PromiseLike<T>) => void;
  const promise = new Promise<T>((resolvePromise) => {
    resolve = resolvePromise;
  });

  return { promise, resolve };
}

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

test('ignores stale and anonymous 401 responses across token ABA changes', async () => {
  const realWindow = window;
  const assign = vi.fn();
  vi.stubGlobal('window', {
    location: {
      assign,
      origin: realWindow.location.origin,
      pathname: '/orders',
    },
  });

  const tokenAResponse = deferred<Response>();
  const tokenAbaResponse = deferred<Response>();
  const anonymousResponse = deferred<Response>();
  const preservedUnauthorizedResponse = deferred<Response>();
  const successfulExitResponse = deferred<Response>();
  const currentResponse = deferred<Response>();
  const fetchMock = vi
    .fn()
    .mockReturnValueOnce(tokenAResponse.promise)
    .mockReturnValueOnce(tokenAbaResponse.promise)
    .mockReturnValueOnce(anonymousResponse.promise)
    .mockReturnValueOnce(preservedUnauthorizedResponse.promise)
    .mockReturnValueOnce(successfulExitResponse.promise)
    .mockReturnValueOnce(currentResponse.promise);
  vi.stubGlobal('fetch', fetchMock);

  setToken('token-a');
  const tokenARequest = request('/orders');
  setToken('token-b');
  tokenAResponse.resolve(new Response(null, { status: 401 }));
  await expect(tokenARequest).rejects.toMatchObject({ status: 401 });
  expect(getToken()).toBe('token-b');
  expect(assign).not.toHaveBeenCalled();

  setToken('token-a');
  const tokenAbaRequest = request('/settings');
  setToken('token-b');
  setToken('token-a');
  tokenAbaResponse.resolve(new Response(null, { status: 401 }));
  await expect(tokenAbaRequest).rejects.toMatchObject({ status: 401 });
  expect(getToken()).toBe('token-a');
  expect(assign).not.toHaveBeenCalled();

  setToken('merchant-token');
  const anonymousRequest = request('/stores/public', { auth: false });
  anonymousResponse.resolve(new Response(null, { status: 401 }));
  await expect(anonymousRequest).rejects.toMatchObject({ status: 401 });
  expect(getToken()).toBe('merchant-token');
  expect(assign).not.toHaveBeenCalled();

  const impersonationExit = authApi.endImpersonation();
  preservedUnauthorizedResponse.resolve(new Response(null, { status: 401 }));
  await expect(impersonationExit).rejects.toMatchObject({ status: 401 });
  expect(getToken()).toBe('merchant-token');
  expect(assign).not.toHaveBeenCalled();
  expect(fetchMock.mock.calls[3]?.[0]).toBe(`${realWindow.location.origin}/api/v1/auth/logout`);
  expect(fetchMock.mock.calls[3]?.[1]).toMatchObject({
    method: 'POST',
    headers: { Authorization: 'Bearer merchant-token' },
  });

  const successfulExit = authApi.endImpersonation();
  successfulExitResponse.resolve(new Response(null, { status: 204 }));
  await expect(successfulExit).resolves.toBeUndefined();
  expect(getToken()).toBe('merchant-token');

  const currentRequest = request('/auth/me');
  currentResponse.resolve(new Response(null, { status: 401 }));
  await expect(currentRequest).rejects.toMatchObject({ status: 401 });
  expect(getToken()).toBeNull();
  expect(assign).toHaveBeenCalledOnce();
  expect(assign).toHaveBeenCalledWith('/login');
});
