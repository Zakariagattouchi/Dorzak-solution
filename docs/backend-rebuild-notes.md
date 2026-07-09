# Backend Rebuild Guide & API Contracts

This guide specifies how to implement a real backend server (Node.js/Express, Python/FastAPI, Go, or Ruby) to replace `src/api/mockApi.ts`.

---

## 1. REST Endpoints Specification

### Account Info
- **Route**: `GET /api/v1/account`
- **Response**:
```json
{
  "id": "6mxHACXrknbFUl",
  "businessName": "Kyte Official Store",
  "ownerName": "Owner",
  "email": "owner@kyte.site",
  "phone": "+1 555-0199",
  "currency": "USD",
  "taxRate": 8.5,
  "onlineStoreEnabled": true
}
```

### Products CRUD
- `GET /api/v1/products` — Returns array of product items.
- `POST /api/v1/products` — Creates product item.
- `PUT /api/v1/products/:id` — Updates product item.
- `DELETE /api/v1/products/:id` — Deletes product item.

### Customer CRM
- `GET /api/v1/customers` — Returns array of customers.
- `POST /api/v1/customers` — Creates customer entry.

### Orders & Checkout
- `GET /api/v1/orders` — Returns order history.
- `POST /api/v1/orders` — Submits new order transaction and updates inventory stock.

---

## 2. Swapping Mock Client with Real Fetch Client
In `src/api/mockApi.ts`, replace the in-memory array operations with `fetch('/api/v1/...')` or `axios` calls pointing to your API gateway URL.
