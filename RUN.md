# Running the full stack (real test)

The React frontend (`src/`) is wired to the Laravel backend (`backend/`) via
`src/api/apiClient.ts` + `src/api/endpoints.ts` + `src/api/adapters.ts`.
Auth is **Sanctum bearer-token** mode (token kept in `localStorage`) — no cookie/CSRF setup needed.

## 1. Backend (terminal 1)

```bash
cd backend
composer install                 # first time
cp .env.example .env && php artisan key:generate   # first time
touch database/database.sqlite   # local dev uses sqlite
php artisan migrate:fresh --seed # loads the Dorzak Merchant demo store
php artisan serve --host=127.0.0.1 --port=8000
```

Demo login: **merchant@dorzak.com / password** (owner).
Also seeded: `alex@example.com` (cashier), `maria@example.com` (manager) — same password.

## 2. Frontend (terminal 2)

```bash
npm install        # first time
npm run dev        # Vite dev server on http://localhost:3000
```

`.env` points the SPA at `http://127.0.0.1:8000/api/v1` (edit `VITE_API_URL` to change).

Open http://localhost:3000 → you'll be redirected to `/login` → sign in → the app loads
the real store: 6 products (hoodie with 3 variants), 5 categories, 4 customers, 3 orders,
live reports. Make a sale in **Sell**, watch stock deduct in **Products**, see the new order in
**Orders / Transactions**, and the numbers update in **Finances / Analytics**.

## What's wired
- **Auth**: login page, token session, route guard, role-gated sidebar (nav filters by abilities).
- **Reads**: products, categories, customers, orders, settings — all from the API via adapters.
- **Writes**: POS checkout (server recomputes totals + deducts stock), product create, category
  create, customer create/delete, settings save (grouped PUTs), online-store toggle.

## Notes / remaining polish
- Product **edit** and customer **edit** screens aren't built yet (create works); the buttons
  currently route to the create page. Backend endpoints (`PUT /products/{id}`, `PUT /customers/{id}`)
  are ready.
- The storefront **preview** page still reads the authed stores; pointing it at the public
  `/api/public/*` endpoints is a small follow-up (`publicApi` in `endpoints.ts` is ready).
- Production should switch CORS back to explicit origins + consider Sanctum cookie mode
  (see `docs/backend-planning/12-security-plan.md`).

## Subdomain storefront (production infra prerequisites)

Every paid store gets `{slug}.dorzak.com` from day one. Before go-live:

1. **Wildcard DNS** — add `*.dorzak.com → <app-server-IP>` in your DNS provider.
2. **Wildcard TLS cert** — obtain `*.dorzak.com` via Let's Encrypt (`certbot --dns-…`) or your
   cloud provider's cert manager. Attach to the Nginx/Caddy vhost.
3. **`APP_DOMAIN` env var** — set `APP_DOMAIN=dorzak.com` in `backend/.env` (production).
   The subdomain resolution endpoint (`GET /api/v1/public/resolve`) reads this.
4. **Nginx catch-all** — a single server block `server_name *.dorzak.com dorzak.com;` routes
   all subdomains to the same Laravel app; the API resolves the store from the Host header.
5. **CORS** — `config/cors.php` allowed origins must include `*.dorzak.com` (or use a regex
   pattern via `fruitcake/laravel-cors` `allowed_origins_patterns`).

Free stores have no subdomain — their public surface is the anonymous menu at
`GET /api/v1/public/menu/{menu_token}`.
