// Real API client for the Laravel backend (replaces the in-memory mockApi.ts).
//
// Auth: Sanctum personal-access TOKEN mode. Login returns a bearer token which we
// keep in localStorage and send as `Authorization: Bearer …`. This avoids any
// CSRF/cookie-domain coupling between the SPA origin and the API origin, which makes
// local "real test" runs robust. (Production may prefer Sanctum SPA cookie mode —
// see docs/backend-planning/12-security-plan.md.)

const API_BASE = (import.meta as any).env?.VITE_API_URL || '/api/v1';
const TOKEN_KEY = 'dorzak-token';
let authSessionRevision = 0;

export interface ApiError {
  status: number;
  message: string;
  errors?: Record<string, string[]>;
  code?: string;
  details?: unknown;
}

export function getToken(): string | null {
  try {
    return localStorage.getItem(TOKEN_KEY);
  } catch {
    return null;
  }
}
export function advanceAuthSessionRevision(): void {
  authSessionRevision += 1;
}
export function setToken(token: string | null): void {
  advanceAuthSessionRevision();
  try {
    if (token) localStorage.setItem(TOKEN_KEY, token);
    else localStorage.removeItem(TOKEN_KEY);
  } catch {
    /* ignore */
  }
}

function clearAuthenticationForRevision(revision: number): void {
  if (revision !== authSessionRevision) return;
  setToken(null);
  if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
    window.location.assign('/login');
  }
}

type Options = {
  method?: string;
  body?: unknown;
  params?: Record<string, string | number | boolean | undefined>;
  base?: string; // override base (e.g. public storefront)
  auth?: boolean; // attach bearer token (default true)
  clearAuthenticationOnUnauthorized?: boolean; // default true
};

export async function request<T = unknown>(path: string, opts: Options = {}): Promise<T> {
  const method = opts.method ?? 'GET';
  const base = opts.base ?? API_BASE;

  const url = new URL(
    `${base}${path}`,
    base.startsWith('http') ? undefined : window.location.origin,
  );
  if (opts.params) {
    for (const [k, v] of Object.entries(opts.params)) {
      if (v !== undefined && v !== '') url.searchParams.set(k, String(v));
    }
  }

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  const usesAuthentication = opts.auth !== false;
  const requestAuthRevision = authSessionRevision;
  const token = usesAuthentication ? getToken() : null;
  if (token) headers.Authorization = `Bearer ${token}`;

  let body: BodyInit | undefined;
  if (opts.body instanceof FormData) {
    body = opts.body;
  } else if (opts.body !== undefined) {
    headers['Content-Type'] = 'application/json';
    body = JSON.stringify(opts.body);
  }

  const res = await fetch(url.toString(), { method, headers, body });

  if (res.status === 401) {
    if (usesAuthentication && opts.clearAuthenticationOnUnauthorized !== false) {
      clearAuthenticationForRevision(requestAuthRevision);
    }
    throw normalize(res, { message: 'Unauthenticated.' });
  }

  if (!res.ok) {
    const payload = await res.json().catch(() => ({}));
    throw normalize(res, payload);
  }

  if (res.status === 204) return undefined as T;
  return (await res.json()) as T;
}

/** Fetch an authenticated file (e.g. a CSV export) and trigger a browser download. */
export async function downloadFile(path: string, filename: string, base?: string): Promise<void> {
  const requestAuthRevision = authSessionRevision;
  const token = getToken();
  const b = base ?? API_BASE;
  const url = b.startsWith('http') ? `${b}${path}` : `${window.location.origin}${b}${path}`;
  const res = await fetch(url, { headers: token ? { Authorization: `Bearer ${token}` } : {} });
  if (res.status === 401) {
    clearAuthenticationForRevision(requestAuthRevision);
  }
  if (!res.ok) throw normalize(res, await res.json().catch(() => ({})));
  const blob = await res.blob();
  const href = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = href;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(href);
}

export async function requestBlob(path: string): Promise<Blob> {
  const requestAuthRevision = authSessionRevision;
  const token = getToken();
  // Use the shared API_BASE: an empty VITE_API_URL must fall back to /api/v1
  // (`||`, not `??` — the env var is "" in dev, which `??` would let through
  // and drop the /api/v1 prefix, 404-ing every proof/blob fetch).
  const base = API_BASE;
  const url = base.startsWith('http')
    ? `${base}${path}`
    : `${window.location.origin}${base}${path}`;
  const response = await fetch(url, {
    headers: {
      Accept: 'image/*',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  });
  if (response.status === 401) {
    clearAuthenticationForRevision(requestAuthRevision);
  }
  if (!response.ok) throw normalize(response, { message: 'Payment proof is unavailable.' });
  return response.blob();
}

function normalize(res: Response, payload: any): ApiError {
  return {
    status: res.status,
    message: payload?.message ?? 'Something went wrong.',
    errors: payload?.errors,
    code: payload?.code,
    details: payload?.details,
  };
}
