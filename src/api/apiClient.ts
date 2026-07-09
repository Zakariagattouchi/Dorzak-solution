// Real API client for the Laravel backend (replaces the in-memory mockApi.ts).
//
// Auth: Sanctum personal-access TOKEN mode. Login returns a bearer token which we
// keep in localStorage and send as `Authorization: Bearer …`. This avoids any
// CSRF/cookie-domain coupling between the SPA origin and the API origin, which makes
// local "real test" runs robust. (Production may prefer Sanctum SPA cookie mode —
// see docs/backend-planning/12-security-plan.md.)

const API_BASE = (import.meta as any).env?.VITE_API_URL || '/api/v1';
const TOKEN_KEY = 'dorzak-token';

export interface ApiError {
  status: number;
  message: string;
  errors?: Record<string, string[]>;
  code?: string;
  details?: unknown;
}

export function getToken(): string | null {
  try { return localStorage.getItem(TOKEN_KEY); } catch { return null; }
}
export function setToken(token: string | null): void {
  try {
    if (token) localStorage.setItem(TOKEN_KEY, token);
    else localStorage.removeItem(TOKEN_KEY);
  } catch { /* ignore */ }
}

type Options = {
  method?: string;
  body?: unknown;
  params?: Record<string, string | number | boolean | undefined>;
  base?: string; // override base (e.g. public storefront)
  auth?: boolean; // attach bearer token (default true)
};

export async function request<T = unknown>(path: string, opts: Options = {}): Promise<T> {
  const method = opts.method ?? 'GET';
  const base = opts.base ?? API_BASE;

  const url = new URL(`${base}${path}`, base.startsWith('http') ? undefined : window.location.origin);
  if (opts.params) {
    for (const [k, v] of Object.entries(opts.params)) {
      if (v !== undefined && v !== '') url.searchParams.set(k, String(v));
    }
  }

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  const token = getToken();
  if (opts.auth !== false && token) headers.Authorization = `Bearer ${token}`;

  let body: BodyInit | undefined;
  if (opts.body instanceof FormData) {
    body = opts.body;
  } else if (opts.body !== undefined) {
    headers['Content-Type'] = 'application/json';
    body = JSON.stringify(opts.body);
  }

  const res = await fetch(url.toString(), { method, headers, body });

  if (res.status === 401) {
    setToken(null);
    if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
      window.location.assign('/login');
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
  const token = getToken();
  const b = base ?? API_BASE;
  const url = b.startsWith('http') ? `${b}${path}` : `${window.location.origin}${b}${path}`;
  const res = await fetch(url, { headers: token ? { Authorization: `Bearer ${token}` } : {} });
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
  const token = getToken();
  const base = (import.meta as any).env?.VITE_API_URL ?? '/api/v1';
  const url = base.startsWith('http') ? `${base}${path}` : `${window.location.origin}${base}${path}`;
  const response = await fetch(url, {
    headers: {
      Accept: 'image/*',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  });
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
